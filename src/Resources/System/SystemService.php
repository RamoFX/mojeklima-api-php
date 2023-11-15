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
        $redisHealthy = boolval($this->redis->ping());
      } catch (Throwable) {
        $redisHealthy = false;
      }

      $databaseHealthy = $this->entityManager->getConnection()->isConnected();

      return new HealthOutput(
        $redisHealthy,
        $databaseHealthy
      );
    }
  }
}
