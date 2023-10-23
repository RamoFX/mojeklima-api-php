<?php



namespace App\Resources\Common\Utilities {

  use Exception;



  class Random {
    /**
     * @throws Exception
     */
    public static function randomString(int $length, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string {
      $charactersLength = strlen($characters);
      $randomString = '';

      for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
      }

      return $randomString;
    }
  }
}
