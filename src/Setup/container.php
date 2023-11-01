<?php



namespace App\Setup {

  use DI\ContainerBuilder;



  $containerDefinitions = require SETUP_PATH . '/containerDefinitions.php';
  $config = require SETUP_PATH . '/config.php';



  $containerBuilder = new ContainerBuilder();

  $containerBuilder->addDefinitions($containerDefinitions);

  if ($config['is']['prod']) {
    $containerBuilder->enableCompilation(CONTAINER_COMPILATION_PATH);
  }

  $container = $containerBuilder->build();

  return $container;
}
