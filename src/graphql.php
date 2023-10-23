<?php



namespace App {

  use App\Resources\Account\AccountController;
  use App\Resources\Account\AccountService;
  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Alert\AlertController;
  use App\Resources\Alert\AlertService;
  use App\Resources\Alert\Enums\Criteria;
  use App\Resources\Auth\AuthController;
  use App\Resources\Auth\AuthService;
  use App\Resources\Common\Types\EnumType;
  use App\Resources\Common\Utilities\Debug;
  use App\Resources\Common\Utilities\GlobalProxy;
  use App\Resources\Common\Utilities\Translation;
  use App\Resources\Location\LocationController;
  use App\Resources\Location\LocationService;
  use App\Resources\Notification\NotificationController;
  use App\Resources\Notification\NotificationService;
  use App\Resources\PushSubscription\PushSubscriptionController;
  use App\Resources\PushSubscription\PushSubscriptionService;
  use App\Resources\Suggestion\SuggestionController;
  use App\Resources\Suggestion\SuggestionService;
  use App\Resources\Weather\WeatherController;
  use App\Resources\Weather\WeatherService;
  use Doctrine\DBAL\DriverManager;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\ORMSetup;
  use GraphQL\Error\DebugFlag;
  use GraphQL\GraphQL;
  use Predis\Client;
  use Symfony\Component\Cache\Adapter\FilesystemAdapter;
  use Symfony\Component\Cache\Psr16Cache;
  use Symfony\Component\DependencyInjection\Container;
  use TheCodingMachine\GraphQLite\Context\Context;
  use TheCodingMachine\GraphQLite\SchemaFactory;



  // helpers
  $isDevMode = $_ENV["APP_MODE"] === "development";

  function respond($output): void {
    header('Content-Type: application/json');
    echo json_encode($output, JSON_INVALID_UTF8_IGNORE);
  }



  // global exception handler
  set_exception_handler(function(\Throwable $exception) use ($isDevMode) {
    header('Content-Type: application/json');
    $language = Translation::getPreferredLanguage();
    $messages = [
      "cs" => "Vnitřní chyba serveru",
      "en" => "Internal server error",
      "de" => "Interner Serverfehler",
    ];
    $translated_message = Translation::translate($messages, $language);

    $output = [
      "errors" => [
        [
          "message" => $isDevMode
            ? $exception->getMessage()
            : $translated_message
        ]
      ]
    ];

    if ($isDevMode)
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
    'charset' => 'utf8'
  ];

  EnumType::addEnumType(AccountRole::class);
  EnumType::addEnumType(Criteria::class);

  $configuration = ORMSetup::createAttributeMetadataConfiguration([__DIR__ . "/../Core/Entities"], $isDevMode);
  $connection = DriverManager::getConnection($connectionParameters, $configuration);
  GlobalProxy::$entityManager = new EntityManager($connection, $configuration);



  // redis
  GlobalProxy::$redis = new Client([
    'scheme' => $_ENV["REDIS_SCHEME"],
    'host' => $_ENV["REDIS_HOST"],
    'port' => $_ENV["REDIS_PORT"]
  ]);



  // schema preparation
  $pool = new FilesystemAdapter();
  $cache = new Psr16Cache($pool);
  $context = new Context();



  // dependency injection
  GlobalProxy::$container = new Container();

  $injectables = [
    new AuthController(),
    new AuthService(),

    new AccountController(),
    new AccountService(),

    new PushSubscriptionController(),
    new PushSubscriptionService(),

    new LocationController(),
    new LocationService(),

    new WeatherController(),
    new WeatherService(),

    new AlertController(),
    new AlertService(),

    new NotificationController(),
    new NotificationService(),

    new SuggestionController(),
    new SuggestionService(),
  ];

  foreach ($injectables as $injectable) {
    GlobalProxy::$container->set(get_class($injectable), $injectable);
  }



  // schema
  $securityService = new AuthService();
  $factory = new SchemaFactory($cache, GlobalProxy::$container);

  $factory->addControllerNamespace("App\\Resources\\")
    ->addTypeNamespace("App\\Resources\\")
    ->setAuthenticationService($securityService)
    ->setAuthorizationService($securityService);

  if ($isDevMode) {
    $factory->devMode();
  } else {
    $factory->prodMode();
  }

  $schema = $factory->createSchema();



  // request data
  $rawInput = file_get_contents('php://input');
  $input = json_decode($rawInput, true);
  $query = $input['query'] ?? null;
  $variables = $input['variables'] ?? null;



  // cors
  $allowOrigin = $_ENV["SECURITY_CLIENT_ORIGIN"];

  if ($allowOrigin) {
    header("Access-Control-Allow-Origin: $allowOrigin");
    header("Vary: Origin");
  }

  header('Access-Control-Allow-Methods: POST, OPTIONS');
  header('Access-Control-Allow-Headers: Authorization,Content-Type,Preferred-Language,baggage,sentry-trace');

  if (strtolower($_SERVER['REQUEST_METHOD']) === 'options') {
    exit();
  }



  // processing
  $flags = $isDevMode
    ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE
    : DebugFlag::NONE;
  $result = GraphQL::executeQuery($schema, $query, null, $context, $variables);
  $output = $result->toArray($flags);



  // add debug info
  if ($isDevMode) {
    $output["_debug"] = Debug::getAll();
  }



  // response
  respond($output);
}