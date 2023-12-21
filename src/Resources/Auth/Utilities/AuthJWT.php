<?php



namespace App\Resources\Auth\Utilities {

  use DateTimeImmutable;
  use Exception;
  use Psr\SimpleCache\CacheInterface;
  use Psr\SimpleCache\InvalidArgumentException;



  class AuthJWT {
    public const JWT_PAYLOAD_IDENTITY_KEY = 'accountId';
    protected const CACHE_SUBJECT = 'allowedAuthenticationToken';



    public function __construct(
      protected JWT $jwt,
      protected CacheInterface $cache
    ) {}



    public function createAndAllowAuthToken(int $accountId): string {
      $token = $this->createAuthToken($accountId);
      $this->allowAuthToken($accountId, $token);

      return $token;
    }



    public function isAuthTokenAllowed(int $accountId, string $token): bool {
      $tokens = $this->getAllowedAuthTokens($accountId);

      return in_array($token, $tokens);
    }



    public function disallowAuthToken(int $accountId, string $token): bool {
      $tokens = $this->getAllowedAuthTokens($accountId);
      $tokens = array_filter($tokens, fn(string $currentToken) => $currentToken !== $token);

      return $this->setAllowedAuthTokens($accountId, $tokens);
    }



    public function wipeAllowedAuthTokens(int $accountId): bool {
      return $this->setAllowedAuthTokens($accountId, []);
    }



    protected function createAuthToken(int $accountId): string {
      return $this->jwt->create([
        self::JWT_PAYLOAD_IDENTITY_KEY => $accountId
      ], '15 minutes');
    }



    protected function allowAuthToken(int $accountId, string $token): bool {
      $tokens = $this->getAllowedAuthTokens($accountId);
      $tokens[] = $token;

      return $this->setAllowedAuthTokens($accountId, $tokens);
    }



    /**
     * @return string[]
     */
    protected function getAllowedAuthTokens(int $accountId): array {
      try {
        $cacheKey = $this->createAuthTokenCacheKey($accountId);
        $tokens = $this->cache->get($cacheKey, '[]');

        return array_filter(json_decode($tokens), fn(string $token) => $this->jwt->validate($token));
      } catch (InvalidArgumentException) {
        return [];
      }
    }



    protected function setAllowedAuthTokens(int $accountId, array $tokens): bool {
      try {
        $cacheKey = $this->createAuthTokenCacheKey($accountId);
        $tokens = array_filter($tokens, fn(string $token) => $this->jwt->validate($token));
        $tokensExpire = array_map(fn(string $token) => $this->jwt->getExpiration($token), $tokens);
        $now = new DateTimeImmutable();
        $expiresIn = max($tokensExpire) - $now->getTimestamp();

        $this->cache->set($cacheKey, json_encode($tokens), $expiresIn);

        return true;
      } catch (Exception|InvalidArgumentException) {
        return false;
      }
    }



    protected function createAuthTokenCacheKey(int $accountId): string {
      $cacheSubject = self::CACHE_SUBJECT;

      return "$cacheSubject#$accountId";
    }
  }
}
