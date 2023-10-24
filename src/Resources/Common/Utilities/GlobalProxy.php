<?php



namespace App\Resources\Common\Utilities {

  use DI\Container;
  use Doctrine\ORM\EntityManager;
  use Predis\Client;



  class GlobalProxy {
    public static Client $redis;

    public static Container $container;

    public static EntityManager $entityManager;
  }
}
