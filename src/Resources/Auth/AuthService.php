<?php



namespace App\Resources\Auth {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Account\Exceptions\AccountMarkedAsRemoved;
  use App\Resources\Account\Exceptions\EmailNotFound;
  use App\Resources\Account\Exceptions\EmailNotVerified;
  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\IncorrectPassword;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Auth\InputTypes\LoginInput;
  use App\Resources\Auth\InputTypes\TokenOutput;
  use App\Resources\Auth\Utilities\JWT;
  use DateTimeImmutable;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\EntityRepository;
  use Doctrine\ORM\Exception\NotSupported;
  use Doctrine\ORM\Exception\ORMException;
  use Exception;
  use Psr\SimpleCache\CacheInterface;
  use Psr\SimpleCache\InvalidArgumentException;
  use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
  use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
  use Throwable;



  class AuthService implements AuthenticationServiceInterface, AuthorizationServiceInterface {
    protected EntityRepository $repository;
    protected const BEARER = 'Bearer ';
    protected const AUTH_JWT_ACCOUNT_ID_KEY = 'accountId';
    protected const CACHE_SUBJECT = 'allowedAuthenticationToken';



    /**
     * @throws NotSupported
     * @throws ORMException
     */
    public function __construct(
      protected EntityManager $entityManager,
      protected JWT $jwt,
      protected CacheInterface $cache
    ) {
      $this->repository = $entityManager->getRepository(AccountEntity::class);
    }



    /**
     * @throws BearerTokenMissing
     * @throws AuthorizationHeaderMissing
     */
    protected function getBearerToken(): ?string {
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



    protected function getUserAgent(): ?string {
      return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }



    /**
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws InvalidArgumentException
     * @throws InvalidToken
     * @throws TokenExpired
     */
    public function isLogged(): bool {
      $token = $this->getBearerToken();
      $payload = $this->jwt->decode($token);
      $accountId = (int) $payload[self::AUTH_JWT_ACCOUNT_ID_KEY];
      $userAgent = $this->getUserAgent();
      $cacheKey = $this->createAuthenticationTokenCacheKey($accountId, $userAgent);
      $allowedToken = $this->cache->get($cacheKey, '');

      return $token === $allowedToken;
    }



    public function getUser(): AccountEntity|null {
      try {
        $token = $this->getBearerToken();
        $payload = $this->jwt->decode($token);

        return $this->repository->findOneBy([
          'id' => $payload[self::AUTH_JWT_ACCOUNT_ID_KEY]
        ]);
      } catch (Throwable) {
        return null;
      }
    }



    public function isAllowed(string $right, $subject = null): bool {
      return match ($right) {
        'ACCOUNT_MANAGEMENT' => $this->isAdminAccount() || $this->isSystemAccount(),
        'PUSH_NOTIFICATIONS' => $this->isSystemAccount(),
        default => false
      };
    }



    protected function isAdminAccount(): bool {
      return $this->getUser()->getRole() === AccountRole::ADMIN;
    }



    protected function isSystemAccount(): bool {
      return $this->getUser()->getRole() === AccountRole::SYSTEM;
    }



    /**
     * @throws AccountMarkedAsRemoved
     * @throws EmailNotFound
     * @throws IncorrectPassword
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws EmailNotVerified
     * @throws InvalidArgumentException
     */
    public function login(LoginInput $login): TokenOutput {
      try {
        /** @var $account AccountEntity */
        $account = $this->repository->createQueryBuilder('a')
          ->select('a')
          ->where('a.email = :email')
          ->setParameter('email', $login->email)
          ->getQuery()
          ->getSingleResult();
      } catch (Exception) {
        throw new EmailNotFound();
      }

      if (!$account->getEmailVerified())
        throw new EmailNotVerified();

      if ($account->getIsMarkedAsRemoved())
        throw new AccountMarkedAsRemoved();

      $doPasswordsMatch = password_verify($login->password, $account->getPasswordHash());

      if (!$doPasswordsMatch)
        throw new IncorrectPassword();

      return $this->createAuthenticationToken($account->getId());
    }



    /**
     * @throws InvalidArgumentException
     */
    public function logout(): bool {
      try {
        $token = $this->getBearerToken();
        $payload = $this->jwt->decode($token);
        $accountId = (int) $payload[self::AUTH_JWT_ACCOUNT_ID_KEY];
        $userAgent = $this->getUserAgent();
        $cacheKey = $this->createAuthenticationTokenCacheKey($accountId, $userAgent);

        return $this->cache->delete($cacheKey);
      } catch (Throwable) {
        return false;
      }
    }



    /**
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws InvalidArgumentException
     */
    public function renewToken(): TokenOutput {
      return $this->createAuthenticationToken();
    }



    /**
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws InvalidArgumentException
     */
    protected function createAuthenticationToken(int $id = null): TokenOutput {
      $id ??= $this->getUser()->getId();
      $token = $this->jwt->create([ self::AUTH_JWT_ACCOUNT_ID_KEY => $id ]);
      $decodedToken = $this->jwt->decode($token);
      $expiresAt = (int) $decodedToken['exp'];
      $nextRenewalAt = $expiresAt - 60;

      // TokenOutput
      $tokenOutput = new TokenOutput();
      $tokenOutput->token = $token;
      $tokenOutput->nextRenewalAt = new DateTimeImmutable("@$nextRenewalAt");

      // cache
      $userAgent = $this->getUserAgent();
      $cacheKey = $this->createAuthenticationTokenCacheKey($id, $userAgent);
      $now = new DateTimeImmutable();
      $expiresIn = $expiresAt - $now->getTimestamp();
      $this->cache->set($cacheKey, $token, $expiresIn);

      return $tokenOutput;
    }



    protected function createAuthenticationTokenCacheKey(int $accountId, string $userAgent): string {
      $cacheSubject = self::CACHE_SUBJECT;
      $userAgent = urlencode($userAgent);

      return "$cacheSubject#$accountId#$userAgent";
    }
  }
}
