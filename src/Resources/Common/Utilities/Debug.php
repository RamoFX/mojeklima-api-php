<?php



namespace App\Resources\Common\Utilities {

  class Debug {
    private static array $data = [];



    public static function clear(): Debug {
      self::$data = [];

      return new Debug();
    }



    public static function set(string $name, $data): Debug {
      self::$data[$name] = $data;

      return new Debug();
    }



    public static function get(string $name, mixed $default = null) {
      return self::$data[$name] ?? $default;
    }



    public static function getAll(): array {
      return self::$data;
    }
  }
}
