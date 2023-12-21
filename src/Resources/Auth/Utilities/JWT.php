<?php



namespace App\Resources\Auth\Utilities {

  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Common\Utilities\ConfigManager;
  use DateTimeImmutable;
  use Exception;
  use Firebase\JWT\ExpiredException;
  use Firebase\JWT\JWT as JWTCore;
  use Firebase\JWT\Key;



  class JWT {
    private const ALG = 'HS512';



    public function __construct(
      protected ConfigManager $config
    ) {}



    /**
     * @param string $humanReadableDuration eg. '15 minutes'
     */
    public function create(array $payload, string $humanReadableDuration): string {
      $now = new DateTimeImmutable();
      $expireAt = $now->modify("+$humanReadableDuration");

      $payload = [
        'iat' => $now->getTimestamp(),
        'exp' => $expireAt->getTimestamp(),
        'ttl' => $expireAt->getTimestamp() - $now->getTimestamp(),
        ...$payload
      ];

      return JWTCore::encode(
        $payload,
        $this->config->get('security.secret'),
        self::ALG
      );
    }



    /**
     * @throws InvalidToken
     * @throws TokenExpired
     */
    public function decode(string $token): array {
      try {
        $key = new Key(
          $this->config->get('security.secret'),
          self::ALG
        );
        $stdClass = JWTCore::decode($token, $key);

        return json_decode(json_encode($stdClass), true);
      } catch (ExpiredException) {
        throw new TokenExpired();
      } catch (Exception) {
        throw new InvalidToken();
      }
    }
  }
}
