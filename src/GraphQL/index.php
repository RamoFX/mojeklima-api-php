<?php



namespace App\GraphQL {
  use App\Core\EntityManagerProxy;
  use App\GraphQL\Controllers\AlertController;
  use App\GraphQL\Controllers\LocationController;
  use App\GraphQL\Controllers\NotificationController;
  use App\GraphQL\Controllers\OpenCageGeocodingApiController;
  use App\GraphQL\Controllers\PrivateAccountController;
  use App\GraphQL\Controllers\PublicAccountController;
  use App\GraphQL\Controllers\OpenWeatherApiController;
  use App\GraphQL\Controllers\PushSubscriptionController;
  use App\GraphQL\Proxies\ContainerProxy;
  use App\GraphQL\Services\HeadersService;
  use App\GraphQL\Services\SecurityService;
  use App\Utilities\Translation;
  use Doctrine\DBAL\DriverManager;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\ORMSetup;
  use Exception;
  use GraphQL\Error\DebugFlag;
  use GraphQL\GraphQL;
  use Symfony\Component\Cache\Adapter\FilesystemAdapter;
  use Symfony\Component\Cache\Psr16Cache;
  use Symfony\Component\DependencyInjection\Container;
  use TheCodingMachine\GraphQLite\SchemaFactory;



  // initialization

  // helpers
  $is_dev_mode = $_ENV["APP_MODE"] === "development";

  function respond($output) {
    header('Content-Type: application/json');
    echo json_encode($output, JSON_INVALID_UTF8_IGNORE);
  }



  // global exception handler
  set_exception_handler(function(Exception $exception) use ($is_dev_mode) {
    header('Content-Type: application/json');
    $language = Translation::getPreferredLanguage();
    $messages = [
      "cs" => "Vnitřní chyba serveru",
      "en" => "Internal server error",
      "de" => "Interner Serverfehler",
    ];
    $translatedMessage = Translation::translate($messages, $language);

    $output = [
      "errors" => [
        [
          "message" => $is_dev_mode
            ? $exception->getMessage()
            : $translatedMessage
        ]
      ]
    ];

    if ($is_dev_mode)
      $output["trace"] = $exception->getTrace();

    respond($output);
  });



  // orm
  $connectionParameters = [
    "dbname" => $_ENV["DB_DATABASE"],
    "user" => $_ENV["DB_USERNAME"],
    "password" => $_ENV["DB_PASSWORD"],
    "host" => $_ENV["DB_HOSTNAME"],
    "driver" => "pdo_mysql",
    'charset'  => 'utf8'
  ];

  $configuration = ORMSetup::createAnnotationMetadataConfiguration([__DIR__ . "/../Core/Entities"], $is_dev_mode);
  $connection = DriverManager::getConnection($connectionParameters, $configuration);
  $entityManager = new EntityManager($connection, $configuration);
  EntityManagerProxy::$entity_manager = $entityManager;



  // cache
  $pool = new FilesystemAdapter();
  $cache = new Psr16Cache($pool);



  // container
  ContainerProxy::$container = new Container();

  $controllers = [
    new PublicAccountController(),
    new PrivateAccountController(),
    new LocationController(),
    new AlertController(),
    new PushSubscriptionController(),
    new NotificationController(),
    new OpenWeatherApiController(),
    new OpenCageGeocodingApiController()
  ];

  foreach ($controllers as $controller) {
    ContainerProxy::$container->set(get_class($controller), $controller);
  }



  // context
  $context = [];



  // schema
  $factory = new SchemaFactory($cache, ContainerProxy::$container);

  $factory->addControllerNamespace("App\\GraphQL\\Controllers\\")
    ->addTypeNamespace("App\\Core\\Entities\\")
    ->addTypeNamespace("App\\Core\\Enums\\")
    ->addTypeNamespace("App\\GraphQL\\InputTypes\\");

  $securityService = new SecurityService();
  $factory->setAuthenticationService($securityService);
  $factory->setAuthorizationService($securityService);

  $schema = $factory->createSchema();



  // request
  $rawInput = file_get_contents('php://input');
  $input = json_decode($rawInput, true);
  $query = $input['query'];
  $variables = $input['variables'] ?? null;



  // pre-processing

  // CORS
  $allowOrigin = $_ENV["SECURITY_CLIENT_ORIGIN"];

  if ($allowOrigin) {
    header("Access-Control-Allow-Origin: $allowOrigin");
    header("Vary: Origin");
  }

  header('Access-Control-Allow-Methods: POST, OPTIONS');
  header('Access-Control-Allow-Headers: Authorization,Content-Type,Preferred-Language');

  if (strtolower($_SERVER['REQUEST_METHOD']) === 'options')
    exit();



  // processing
  $flags = $is_dev_mode
    ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
    : DebugFlag::NONE;

  $result = GraphQL::executeQuery($schema, $query, null, $context, $variables);

  $output = $result->toArray($flags);



  // format output errors
  if (isset($output["errors"])) {
    $formattedErrors = [];

    foreach ($output["errors"] as $error) {
      $formattedError = $error;

      unset($formattedError["extensions"]);
      unset($formattedError["locations"]);
      unset($formattedError["path"]);

      $formattedErrors[] = $formattedError;
    }

    $output["errors"] = $formattedErrors;
  }



  // add optional development output from buffer
  if ($is_dev_mode)
    $output["__development"] = DevelopmentOutputBuffer::getAll();



  // response
  respond($output);
}
