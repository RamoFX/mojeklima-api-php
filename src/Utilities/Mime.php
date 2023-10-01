<?php



namespace App\Utilities {
  class Mime {
    public static function image(string $type) {
      return "image/$type";
    }
  }
}
