<?php



namespace App {

  use App\Resources\Auth\AuthService;
  use App\Resources\Common\Utilities\ConfigManager;
  use App\Resources\Common\Utilities\Debug;
  use App\Resources\Common\Utilities\Translation;
  use GraphQL\Error\DebugFlag;
  use GraphQL\GraphQL;
  use Psr\SimpleCache\CacheInterface;
  use TheCodingMachine\GraphQLite\Context\Context;
  use TheCodingMachine\GraphQLite\SchemaFactory;
  use Throwable;



  // helpers
  $container = require SETUP_PATH . '/container.php';
  $config = $container->get(ConfigManager::class);
  $isDev = $config->get('is.dev');
  $isProd = $config->get('is.prod');
  $isDebug = $config->get('is.debug');

  function respond($output): void {
    global $isDebug;

    if ($isDebug) {
      $output["debug"] = Debug::getAll();
    }

    header('Content-Type: application/json');
    echo json_encode($output, JSON_INVALID_UTF8_IGNORE);
  }



  // global exception handler
  set_exception_handler(function(Throwable $exception) {
    global $isProd, $isDev, $isDebug;

    header('Content-Type: application/json');
    $internalErrorMessage = Translation::translate([
      "cs" => "Vnitřní chyba serveru",
      "en" => "Internal server error",
      "de" => "Interner Serverfehler",
    ]);

    $output = [
      "errors" => [
        [
          "message" => $isProd
            ? $internalErrorMessage
            : $exception->getMessage()
        ]
      ]
    ];

      $output["trace"] = $exception->getTrace();
    if ($isDev || $isDebug)

    respond($output);
  });



  // headers
  $allowOrigin = $config->get('security.origin');

  header("Access-Control-Allow-Origin: $allowOrigin");
  header("Vary: Origin");
  header('Access-Control-Allow-Methods: POST, OPTIONS');
  header('Access-Control-Allow-Headers: Authorization,Content-Type,Preferred-Language,baggage,sentry-trace');

  if (strtolower($_SERVER['REQUEST_METHOD']) === 'options') {
    exit();
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
}
