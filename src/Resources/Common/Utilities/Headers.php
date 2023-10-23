<?php



namespace App\Resources\Common\Utilities {



  class Headers {
    public static function get(string $name) {
      $headers = apache_request_headers();

      foreach ($headers as $currentName => $value) {
        $isRightHeader = strtolower($currentName) === strtolower($name);

        if ($isRightHeader)
          return $value;
      }

      return null;
    }
  }
}
