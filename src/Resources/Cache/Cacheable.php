<?php



namespace App\Resources\Cache {



  interface Cacheable {
    /**
     * @param string[] $components
     * @return string
     */
    public static function getKey(string ...$components): string;

    /**
     * @return int
     */
    public static function getExpiration(): int;
  }
}
