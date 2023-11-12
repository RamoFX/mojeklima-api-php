<?php



namespace App\Setup {

  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Alert\Enums\Criteria;
  use App\Resources\Common\Types\EnumType;
  use App\Resources\Common\Utilities\ConfigManager;
  use Doctrine\DBAL\DriverManager;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\ORMSetup;
  use MatthiasMullie\Scrapbook\Adapters\Redis as RedisStore;
  use MatthiasMullie\Scrapbook\Psr16\SimpleCache;
  use Psr\SimpleCache\CacheInterface;
  use Redis as RedisClient;
  use function DI\create;



  $config = require SETUP_PATH . '/config.php';



  return [



    ConfigManager::class => create(ConfigManager::class)->constructor($config),



    EntityManager::class => function(ConfigManager $config) {
      EnumType::addEnumType(AccountRole::class);
      EnumType::addEnumType(Criteria::class);

      $configuration = ORMSetup::createAttributeMetadataConfiguration(
        $config->get('doctrine.entities'),
        $config->get('is.dev')
      );

      $connection = DriverManager::getConnection(
        $config->get('doctrine.connection'),
        $configuration
      );

      return new EntityManager($connection, $configuration);
    },



    CacheInterface::class => function(ConfigManager $config) {
      $client = new RedisClient();

      $client->connect(
        $config->get('redis.hostname'),
        $config->get('redis.port'),
        context: [
          'auth' => [
            $config->get('redis.password')
          ]
        ]
      );

      $store = new RedisStore($client);

      return new SimpleCache($store);
    }



  ];
}
