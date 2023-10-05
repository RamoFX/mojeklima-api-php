<?php



require_once __DIR__ . '/../src/bootstrap.php';

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;



$connection_parameters = [
  "dbname" => $_ENV["DB_DATABASE"],
  "user" => $_ENV["DB_USERNAME"],
  "password" => $_ENV["DB_PASSWORD"],
  "host" => $_ENV["DB_HOSTNAME"],
  "driver" => "pdo_mysql",
  'charset'  => 'utf8'
];

$configuration = ORMSetup::createAnnotationMetadataConfiguration([__DIR__ . "/../src/Core/Entities"], true);
$connection = DriverManager::getConnection($connectionParameters, $configuration);
$entityManager = new EntityManager($connection, $configuration);

return ConsoleRunner::createHelperSet($entityManager);

// to create database tables run: php vendor/bin/doctrine orm:schema-tool:update --force
