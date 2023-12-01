<?php



namespace App\Resources\System {

  use App\Resources\Common\CommonService;
  use App\Resources\System\DTO\HealthOutput;
  use Doctrine\ORM\EntityManager;
  use Redis as RedisClient;
  use Throwable;



  class SystemService extends CommonService {
    public function __construct(
      protected RedisClient $redis,
      protected EntityManager $entityManager
    ) {
      parent::__construct();
    }



    public function health(): HealthOutput {
      try {
        $redis = boolval($this->redis->ping());
      } catch (Throwable) {
        $redis = false;
      }

      try {
        $this->entityManager->getConnection()
          ->prepare('select 1')
          ->executeQuery()
          ->fetchNumeric();
        $database = true;
      } catch (Throwable) {
        $database = false;
      }

      $overall = $redis && $database;

      return new HealthOutput(
        $redis,
        $database,
        $overall
      );
    }
  }
}
