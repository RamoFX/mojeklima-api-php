<?php



namespace App\GraphQL {

  class DevelopmentOutputBuffer {
    private static array $data = [];

    public static function clear(): DevelopmentOutputBuffer {
      self::$data = [];

      return new DevelopmentOutputBuffer();
    }

    public static function set(string $name, $data): DevelopmentOutputBuffer {
      self::$data[$name] = $data;

      return new DevelopmentOutputBuffer();
    }

    public static function getAll(): array {
      return self::$data;
    }
  }
}
