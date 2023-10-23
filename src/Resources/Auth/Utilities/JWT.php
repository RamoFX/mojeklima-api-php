<?php



namespace App\Resources\Auth\Utilities {

  use App\Resources\Auth\AuthService;
  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use DateTimeImmutable;
  use Exception;
  use Firebase\JWT\ExpiredException;
  use Firebase\JWT\JWT as JWTCore;
  use Firebase\JWT\Key;



  class JWT {
    private static string $alg = "HS512";



    // TODO: Where is refresh token?



    public static function createToken(int $id, bool $remember): string {
      $date = new DateTimeImmutable();
      $expireAt = $date->modify('+1 hour')->getTimestamp(); // TODO: Too long

      $payload = [
        'iat' => $date->getTimestamp(),
        'exp' => $expireAt,
        'id' => $id
      ];

      if ($remember)
        unset($payload["exp"]); // TODO: Bad

      return JWTCore::encode($payload, $_ENV["SECURITY_SECRET"], self::$alg);
    }



    /**
     * @throws InvalidToken
     * @throws AuthorizationHeaderMissing
     * @throws TokenExpired
     * @throws BearerTokenMissing
     */
    public static function renewToken(int $id, bool $remember): string {
      $token = AuthService::getBearerToken();
      $payload = self::decodeToken($token);
      $canExpire = in_array("exp", array_keys($payload));

      if ($canExpire || !$remember)
        return self::createToken($id, $remember);

      else
        return $token;
    }



    /**
     * @throws InvalidToken
     * @throws TokenExpired
     */
    public static function decodeToken(string $token): array {
      try {
        $stdClass = JWTCore::decode($token, new Key($_ENV["SECURITY_SECRET"], self::$alg));

        return json_decode(json_encode($stdClass), true);
      } catch (ExpiredException) {
        throw new TokenExpired();
      } catch (Exception) {
        throw new InvalidToken();
      }
    }
  }
}
