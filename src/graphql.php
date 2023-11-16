<?php



namespace App {

  use App\Resources\Auth\AuthService;
  use App\Resources\Common\Utilities\ConfigManager;
  use App\Resources\Common\Utilities\Debug;
  use App\Resources\Common\Utilities\Translation;
  use App\Resources\Limit\RateLimitService;
  use DI\Container;
  use GraphQL\Error\DebugFlag;
  use GraphQL\GraphQL;
  use Psr\SimpleCache\CacheInterface;
  use Psr\SimpleCache\InvalidArgumentException;
  use TheCodingMachine\GraphQLite\Context\Context;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;
  use TheCodingMachine\GraphQLite\SchemaFactory;
  use Throwable;



  try {
    global $isDev;

    function respond($output): void {
      global $isDev;

      if ($isDev ?? false) {
        $output['debug'] = Debug::getAll();
      }

      header('Content-Type: application/json');
      echo json_encode($output, JSON_INVALID_UTF8_IGNORE);
    }



    // helpers
    /** @var $container Container */
    $container = require SETUP_PATH . '/container.php';
    /** @var $config ConfigManager */
    $config = $container->get(ConfigManager::class);
    $isDev = $config->get('is.dev');
    $isProd = $config->get('is.prod');



    // headers
    $allowOrigin = $config->get('security.origin');

    header("Access-Control-Allow-Origin: $allowOrigin");
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization,Content-Type,Preferred-Language,baggage,sentry-trace');

    if (strtolower($_SERVER['REQUEST_METHOD']) === 'options') {
      exit();
    }



    // rate limit
    if ($isProd) {
      $rateLimitService = $container->get(RateLimitService::class);
      $rateLimitService->apply();
    }



    // schema
    $cache = $container->get(CacheInterface::class);
    $factory = new SchemaFactory($cache, $container);
    $controllerNamespace = $config->get('namespace.controller');
    $typeNamespace = $config->get('namespace.type');
    $authService = $container->get(AuthService::class);

    $factory
      ->addControllerNamespace($controllerNamespace)
      ->addTypeNamespace($typeNamespace)
      ->setAuthenticationService($authService)
      ->setAuthorizationService($authService);

    if ($isProd) {
      $factory->prodMode();
    } else {
      $factory->devMode();
    }

    $schema = $factory->createSchema();



    // request data
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    $query = $input['query'] ?? null;
    $variables = $input['variables'] ?? null;



    // processing
    $flags = $isDev
      ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
      : DebugFlag::NONE;
    $context = new Context();
    $result = GraphQL::executeQuery($schema, $query, null, $context, $variables);
    $output = $result->toArray($flags);



    // response
    respond($output);
  } catch (Throwable|InvalidArgumentException $exception) {
    $message = $exception instanceof GraphQLException && $exception->isClientSafe()
      ? $exception->getMessage()
      : Translation::translate([
        'cs' => 'Vnitřní chyba serveru',
        'en' => 'Internal server error',
        'de' => 'Interner Serverfehler'
      ]);

    $error = [
      'message' => $message
    ];

    // TODO: refactor - create a detailed representation on an error like the one below and:
    //  a) in dev - output
    //  b) in prod - log
    if ($isDev ?? false) {
      $originalTrace = $exception->getTrace();
      $customTrace = [];

      foreach ($originalTrace as $trace) {
        $file = str_replace(PROJECT_ROOT_PATH, '', $trace['file'] ?? '');
        $line = $trace['line'] ?? '';
        $function = $trace['function'] ?? '';
        $type = $trace['type'] ?? '';
        $class = $trace['class'] ?? '';
        $args = $trace['args'] ?? null;

        $location = implode(':', [ $file, $line ]);
        $member = "$class$type$function";

        $customTrace[] = [
          'location' => $location, // "location": "/src/graphql.php:158",
          'member' => $member,     // "member":   "\App\Resources\Common\Utilities\Debug::getAll",
          'args' => $args          // "args":     [ ... ]
        ];
      }

      $file = str_replace(PROJECT_ROOT_PATH, '', $exception->getFile() ?? '');
      $line = $exception->getLine();
      $location = implode(':', [ $file, $line ]);

      $error['extensions'] = [
        'debugMessage' => $exception->getMessage(),
        'location' => $location,
        'trace' => $customTrace
      ];
    }

    respond([
      'errors' => [
        $error
      ]
    ]);
  }
}
