<?php



/*

  tree ./mojeklima-api-php/ -L 2 -I ".run|.vscode|logs|playground|vendor|xdebug" -d

  ./mojeklima-api-php/
  ├── compilation
  │   └── container
  ├── config
  ├── src
  │   ├── Resources
  │   └── Setup
  └── uploads
      └── avatars

*/



define("PROJECT_ROOT_PATH", realpath(__DIR__ . '/../../'));
define("COMPILATION_PATH", realpath(PROJECT_ROOT_PATH . '/compilation/'));
define("CONTAINER_COMPILATION_PATH", realpath(COMPILATION_PATH . '/container/'));
define("CONFIG_PATH", realpath(PROJECT_ROOT_PATH . '/config/'));
define("SOURCE_PATH", realpath(PROJECT_ROOT_PATH . '/src/'));
define("RESOURCES_PATH", realpath(SOURCE_PATH . '/Resources/'));
define("SETUP_PATH", realpath(SOURCE_PATH . '/Setup/'));
define("UPLOADS_PATH", realpath(PROJECT_ROOT_PATH . '/uploads/'));
define("AVATAR_UPLOADS_PATH", realpath(UPLOADS_PATH . '/avatars/'));
