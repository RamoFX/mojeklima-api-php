<?php



use Dotenv\Dotenv;



require_once __DIR__ . '/../vendor/autoload.php';


// .env
$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();
