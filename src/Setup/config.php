<?php



namespace App\Setup {

  return [
    'app' => [
      'name' => $_ENV['APP_NAME'],
      'mode' => $_ENV['APP_MODE'],
      'debug' => $_ENV['APP_DEBUG'],
      'origin' => $_ENV['APP_ORIGIN']
    ],
    'is' => [
      'dev' => $_ENV['APP_MODE'] === 'development',
      'prod' => $_ENV['APP_MODE'] === 'production',
      'debug' => $_ENV['APP_DEBUG'] === 'true'
    ],
    'doctrine' => [
      'connection' => [
        'dbname' => $_ENV['DB_DATABASE'],
        'user' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'host' => $_ENV['DB_HOSTNAME'],
        'driver' => $_ENV['DB_DRIVER'],
        'charset' => $_ENV['DB_CHARSET']
      ],
      'entities' => [
        RESOURCES_PATH
      ]
    ],
    'redis' => [
      'hostname' => $_ENV['REDIS_HOSTNAME'],
      'port' => $_ENV['REDIS_PORT'],
      'password' => $_ENV['REDIS_PASSWORD']
    ],
    'security' => [
      'secret' => $_ENV['SECURITY_SECRET'],
      'origin' => $_ENV['SECURITY_CLIENT_ORIGIN']
    ],
    'namespace' => [
      'controller' => 'App\\Resources\\',
      'type' => 'App\\Resources\\'
    ],
    'keys' => [
      'push' => [
        'public' => $_ENV['PUSH_NOTIFICATIONS_PUBLIC_KEY'],
        'private' => $_ENV['PUSH_NOTIFICATIONS_PRIVATE_KEY']
      ],
      'api' => [
        'openWeather' => $_ENV['API_KEY_OPEN_WEATHER'],
        'openCageGeocoding' => $_ENV['API_KEY_OPEN_CAGE_GEOCODING'],
        'brevo' => $_ENV['API_KEY_BREVO']
      ]
    ],
    'email' => [
      'sender' => $_ENV['EMAIL_SENDER'],
      'replyTo' => $_ENV['EMAIL_REPLY_TO']
    ]
  ];
}
