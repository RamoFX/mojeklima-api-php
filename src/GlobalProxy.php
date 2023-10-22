<?php



namespace App {

  use Doctrine\ORM\EntityManager;
  use Predis\Client;
  use Symfony\Component\DependencyInjection\Container;



  class GlobalProxy {
    public static Client $redis;
    public static Container $container;
    public static EntityManager $entityManager;
  }
}
