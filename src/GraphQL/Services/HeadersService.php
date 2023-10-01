<?php



namespace App\GraphQL\Services {

  use App\GraphQL\Exceptions\AuthorizationHeaderMissing;
  use App\GraphQL\Exceptions\BearerTokenMissing;



  class HeadersService {
    public static function get(string $name) {
      $headers = apache_request_headers();

      foreach ($headers as $currentName => $value) {
        $isRightHeader = strtolower($currentName) === strtolower($name);

        if ($isRightHeader)
          return $value;
      }

      return null;
    }



    /**
     * @throws BearerTokenMissing
     * @throws AuthorizationHeaderMissing
     */
    public static function getBearerToken(): string {
      $authorization = self::get("Authorization");

      if ($authorization === null)
        throw new AuthorizationHeaderMissing();

      $isBearer = str_starts_with($authorization, "Bearer");

      if (!$isBearer)
        throw new BearerTokenMissing();

      return substr($authorization, strlen("Bearer "));
    }
  }
}
