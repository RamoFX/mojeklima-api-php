<?php



namespace App\Resources\Common\Utilities {



  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;



  class Headers {
    protected const BEARER = 'Bearer ';



    public static function getClientIp(): string {
      if (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];

      else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];

      else
        return $_SERVER['REMOTE_ADDR'];
    }



    /**
     * @throws BearerTokenMissing
     * @throws AuthorizationHeaderMissing
     */
    public static function getBearerToken(): string {
      $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

      if ($authorization === null)
        throw new AuthorizationHeaderMissing();

      if (!str_starts_with($authorization, self::BEARER))
        throw new BearerTokenMissing();

      $token = trim(
        substr($authorization, strlen(self::BEARER))
      );

      if (strlen($token) === 0)
        throw new BearerTokenMissing();

      return $token;
    }



    public static function getUserAgent(): ?string {
      return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }
  }
}
