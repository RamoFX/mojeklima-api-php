<?php



namespace App\GraphQL\Services {

  use App\GraphQL\Exceptions\AuthorizationHeaderMissing;
  use App\GraphQL\Exceptions\BearerTokenMissing;
  use App\GraphQL\Exceptions\InvalidToken;
  use App\GraphQL\Exceptions\TokenExpired;
  use DateTimeImmutable;
  use Exception;
  use Firebase\JWT\ExpiredException;
  use Firebase\JWT\JWT;
  use Firebase\JWT\Key;



  class JWTService {
    private static string $alg = "HS512";

    public static function createToken(int $id, bool $remember): string {
      $date = new DateTimeImmutable();
      $expireAt = $date->modify('+1 hour')->getTimestamp();

      $payload = [
        'iat' => $date->getTimestamp(),
        'exp' => $expireAt,
        'id' => $id
      ];

      if ($remember)
        unset($payload["exp"]);

      return JWT::encode($payload, $_ENV["SECURITY_SECRET"], self::$alg);
    }

    /**
     * @throws InvalidToken
     * @throws AuthorizationHeaderMissing
     * @throws TokenExpired
     * @throws BearerTokenMissing
     */
    public static function renewToken(int $id, bool $remember): string {
      $token = HeadersService::getBearerToken();
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
        $stdClass = JWT::decode($token, new Key($_ENV["SECURITY_SECRET"], self::$alg));

        return json_decode(json_encode($stdClass), true);
      } catch (ExpiredException) {
        throw new TokenExpired();
      } catch (Exception) {
        throw new InvalidToken();
      }
    }
  }
}
