<?php



define('PROJECT_ROOT_PATH', realpath(__DIR__ . '/../../'));

define('SOURCE_PATH', realpath(PROJECT_ROOT_PATH . '/src/'));
define('RESOURCES_PATH', realpath(SOURCE_PATH . '/Resources/'));
define('SETUP_PATH', realpath(SOURCE_PATH . '/Setup/'));

define('STORAGE_PATH', realpath(PROJECT_ROOT_PATH . '/storage/'));
define('COMPILATION_PATH', realpath(STORAGE_PATH . '/compilation/'));
define('LOGS_PATH', realpath(STORAGE_PATH . '/logs/'));
define('AVATAR_UPLOADS_PATH', realpath(STORAGE_PATH . '/uploads/avatars/'));
