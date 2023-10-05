<?php



require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;



// environment
if (!isset($_ENV['APP_MODE'])) {
  $dotenv = Dotenv::createImmutable(__DIR__ . "/../");
  $dotenv->load();
}
