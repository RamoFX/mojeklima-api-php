<?php



namespace App\Singleton {

  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Account\Enums\PressureUnits;
  use App\Resources\Account\Enums\SpeedUnits;
  use App\Resources\Account\Enums\TemperatureUnits;
  use App\Resources\Alert\Enums\Criteria;
  use App\Resources\Common\Types\EnumType;
  use App\Resources\Common\Utilities\ConfigManager;
  use Doctrine\DBAL\DriverManager;
  use Doctrine\DBAL\Exception;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
  use Doctrine\ORM\ORMSetup;
  use Redis as RedisClient;
  use Symfony\Component\Cache\Adapter\RedisAdapter;



  class EntityManagerSingleton {
    private static EntityManager|null $instance = null;



    /**
     * @throws MissingMappingDriverImplementation
     * @throws Exception
     */
    public static function getInstance(ConfigManager $config, RedisClient $redis): EntityManager {
      if (self::$instance !== null)
        return self::$instance;

      EnumType::addEnumType(AccountRole::class);
      EnumType::addEnumType(Criteria::class);
      EnumType::addEnumType(TemperatureUnits::class);
      EnumType::addEnumType(SpeedUnits::class);
      EnumType::addEnumType(PressureUnits::class);

      $configuration = ORMSetup::createAttributeMetadataConfiguration(
        $config->get('doctrine.entities'),
        $config->get('is.dev'),
        cache: new RedisAdapter($redis)
      );

      $connection = DriverManager::getConnection(
        $config->get('doctrine.connection'),
        $configuration
      );

      self::$instance = new EntityManager($connection, $configuration);

      return self::$instance;
    }
  }
}
