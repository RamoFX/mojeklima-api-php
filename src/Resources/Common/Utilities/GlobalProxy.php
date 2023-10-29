<?php



namespace App\Resources\Common\Utilities {

  use DI\Container;
  use Doctrine\ORM\EntityManager;
  use Redis;



  class GlobalProxy {
    public static Redis $redis;

    public static Container $container;

    public static EntityManager $entityManager;
  }
}
