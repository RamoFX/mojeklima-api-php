<?php



namespace App\Resources\System {

  use App\Resources\Common\CommonService;
  use App\Resources\System\DTO\HealthOutput;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\NoResultException;
  use Redis as RedisClient;
  use RedisException;



  class SystemService extends CommonService {
    public function __construct(
      protected RedisClient $redis,
      protected EntityManager $entityManager
    ) {
      parent::__construct();
    }



    /**
     * @throws NonUniqueResultException
     * @throws RedisException
     * @throws NoResultException
     */
    public function health(): HealthOutput {
      $redisHealthy = boolval($this->redis->ping());
      $databaseHealthy = $this->entityManager->getConnection()->isConnected();

      return new HealthOutput(
        $redisHealthy,
        $databaseHealthy
      );
    }
  }
}
