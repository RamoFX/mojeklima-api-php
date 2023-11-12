<?php



namespace App\Resources\Limit\Enums {

  enum RateLimitOptions {
    public const COMMON = [
      'limit' => 60,
      'interval' => 60
    ];

    public const SENSITIVE = [
      'limit' => 4,
      'interval' => 60 * 60
    ];
  }
}
