<?php



namespace App\Setup {

  use App\Resources\Common\Utilities\ConfigManager;
  use App\Singleton\EntityManagerSingleton;
  use Doctrine\ORM\EntityManager;
  use MatthiasMullie\Scrapbook\Adapters\Redis as RedisStore;
  use MatthiasMullie\Scrapbook\Psr16\SimpleCache;
  use Psr\SimpleCache\CacheInterface;
  use Redis as RedisClient;
  use function DI\create;



  $config = require SETUP_PATH . '/config.php';



  return [



    ConfigManager::class => create(ConfigManager::class)->constructor($config),



    EntityManager::class => function(ConfigManager $config, RedisClient $redis) {
      return EntityManagerSingleton::getInstance($config, $redis);
    },



    RedisClient::class => function(ConfigManager $config) {
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

      return $client;
    },



    RedisStore::class => function(RedisClient $client) {
      return new RedisStore($client);
    },



    CacheInterface::class => function(RedisStore $store) {
      return new SimpleCache($store);
    }



  ];
}
