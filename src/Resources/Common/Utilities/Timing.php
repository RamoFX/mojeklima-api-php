<?php



namespace App\Resources\Common\Utilities {

  class Timing {
    protected static array $timingStack = [];

    public static function start(): void {
      self::$timingStack[] = microtime(true);
    }

    public static function stop() {
      $durationMicroseconds = microtime(true) - array_pop(self::$timingStack);
      $durationMilliseconds = round($durationMicroseconds * 1000, 1);

      return $durationMilliseconds;
    }
  }
}
