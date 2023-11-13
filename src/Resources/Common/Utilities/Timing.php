<?php



namespace App\Resources\Common\Utilities {

  class Timing {
    protected static array $timingStack = [];



    public static function start(): void {
      self::$timingStack[] = microtime(true);
    }



    public static function stop(): float {
      return round(
        (microtime(true) - array_pop(self::$timingStack)) * 1000,
        1
      );
    }
  }
}
