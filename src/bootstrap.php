<?php



require_once __DIR__ . '/Setup/paths.php';
require_once PROJECT_ROOT_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;



// environment
$dotenv = Dotenv::createImmutable(PROJECT_ROOT_PATH);
$dotenv->load();
