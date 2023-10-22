<?php



namespace App\GraphQL {

  use App\Core\Enums\AccountRole;
  use App\Core\Enums\Criteria;
  use App\Doctrine\EnumType;
  use App\GlobalProxy;
  use App\GraphQL\Controllers\AlertController;
  use App\GraphQL\Controllers\LocationController;
  use App\GraphQL\Controllers\NotificationController;
  use App\GraphQL\Controllers\OpenCageGeocodingApiController;
  use App\GraphQL\Controllers\OpenWeatherApiController;
  use App\GraphQL\Controllers\PrivateAccountController;
  use App\GraphQL\Controllers\PublicAccountController;
  use App\GraphQL\Controllers\PushSubscriptionController;
  use App\GraphQL\Services\SecurityService;
  use App\Utilities\Translation;
  use Doctrine\DBAL\DriverManager;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\ORMSetup;
  use Exception;
  use GraphQL\Error\DebugFlag;
  use GraphQL\GraphQL;
  use Predis\Client;
  use Symfony\Component\Cache\Adapter\FilesystemAdapter;
  use Symfony\Component\Cache\Psr16Cache;
  use Symfony\Component\DependencyInjection\Container;
  use TheCodingMachine\GraphQLite\SchemaFactory;



  // helpers
  $is_dev_mode = $_ENV["APP_MODE"] === "development";

  function respond($output): void {
    header('Content-Type: application/json');
    echo json_encode($output, JSON_INVALID_UTF8_IGNORE);
  }



  // global exception handler
  set_exception_handler(function(Exception $exception) use ($is_dev_mode) {
    header('Content-Type: application/json');
    $language = Translation::get_preferred_language();
    $messages = [
      "cs" => "Vnitřní chyba serveru",
      "en" => "Internal server error",
      "de" => "Interner Serverfehler",
    ];
    $translated_message = Translation::translate($messages, $language);

    $output = [
      "errors" => [
        [
          "message" => $is_dev_mode
            ? $exception->getMessage()
            : $translated_message
        ]
      ]
    ];

    if ($is_dev_mode)
      $output["trace"] = $exception->getTrace();

    respond($output);
  });



  // orm
  $connection_parameters = [
    "dbname" => $_ENV["DB_DATABASE"],
    "user" => $_ENV["DB_USERNAME"],
    "password" => $_ENV["DB_PASSWORD"],
    "host" => $_ENV["DB_HOSTNAME"],
    "driver" => "pdo_mysql",
    'charset' => 'utf8'
  ];

  EnumType::addEnumType(AccountRole::class);
  EnumType::addEnumType(Criteria::class);

  $configuration = ORMSetup::createAttributeMetadataConfiguration([__DIR__ . "/../Core/Entities"], $is_dev_mode);
  $connection = DriverManager::getConnection($connection_parameters, $configuration);
  GlobalProxy::$entityManager = new EntityManager($connection, $configuration);



  // redis
  GlobalProxy::$redis = new Client([
    'scheme' => $_ENV["REDIS_SCHEME"],
    'host' => $_ENV["REDIS_HOST"],
    'port' => $_ENV["REDIS_PORT"]
  ]);



  // cache
  $pool = new FilesystemAdapter();
  $cache = new Psr16Cache($pool);



  // container
  GlobalProxy::$container = new Container();

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
    GlobalProxy::$container->set(get_class($controller), $controller);
  }



  // schema
  $factory = new SchemaFactory($cache, GlobalProxy::$container);

  $factory->addControllerNamespace("App\\GraphQL\\Controllers\\")
    ->addTypeNamespace("App\\Core\\Entities\\")
    ->addTypeNamespace("App\\Core\\Enums\\")
    ->addTypeNamespace("App\\GraphQL\\InputTypes\\");

  $security_service = new SecurityService();
  $factory->setAuthenticationService($security_service);
  $factory->setAuthorizationService($security_service);

  $schema = $factory->createSchema();



  // request data
  $raw_input = file_get_contents('php://input');
  $input = json_decode($raw_input, true);

  $query = $input['query'] ?? null;
  $variables = $input['variables'] ?? null;



  // cors
  $allow_origin = $_ENV["SECURITY_CLIENT_ORIGIN"];

  if ($allow_origin) {
    header("Access-Control-Allow-Origin: $allow_origin");
    header("Vary: Origin");
  }

  header('Access-Control-Allow-Methods: POST, OPTIONS');
  header('Access-Control-Allow-Headers: Authorization,Content-Type,Preferred-Language,baggage,sentry-trace');

  if (strtolower($_SERVER['REQUEST_METHOD']) === 'options') {
    exit();
  }



  // processing
  $flags = $is_dev_mode
    ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
    : DebugFlag::NONE;
  $result = GraphQL::executeQuery($schema, $query, null, [], $variables);
  $output = $result->toArray($flags);



  // add optional development output from buffer
  if ($is_dev_mode) {
    $output["__development"] = DevelopmentOutputBuffer::getAll();
  }



  // response
  respond($output);
}
