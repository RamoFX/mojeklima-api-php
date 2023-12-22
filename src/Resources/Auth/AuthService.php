<?php



namespace App\Resources\Auth {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Account\Exceptions\AccountMarkedAsRemoved;
  use App\Resources\Account\Exceptions\EmailNotFound;
  use App\Resources\Account\Exceptions\EmailNotVerified;
  use App\Resources\Auth\DTO\LoginInput;
  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\IncorrectPassword;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\NotAuthenticated;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Auth\Utilities\AuthJWT;
  use App\Resources\Auth\Utilities\JWT;
  use App\Resources\Common\Utilities\Headers;
  use App\Resources\Permission\Enums\Permission;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\EntityRepository;
  use Doctrine\ORM\Exception\NotSupported;
  use Doctrine\ORM\Exception\ORMException;
  use Exception;
  use Psr\SimpleCache\CacheInterface;
  use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
  use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
  use Throwable;



  class AuthService implements AuthenticationServiceInterface, AuthorizationServiceInterface {
    protected EntityRepository $repository;



    /**
     * @throws NotSupported
     * @throws ORMException
     */
    public function __construct(
      protected EntityManager $entityManager,
      protected JWT $jwt,
      protected CacheInterface $cache,
      protected AuthJWT $authTokenManager
    ) {
      $this->repository = $entityManager->getRepository(AccountEntity::class);
    }



    public function isLogged(): bool {
      try {
        $this->ensureTrustedIdentity();

        $token = Headers::getBearerToken();
        $payload = $this->jwt->decode($token);
        $accountId = (int) $payload[AuthJWT::JWT_PAYLOAD_IDENTITY_KEY];

        return $this->authTokenManager->isAuthTokenAllowed($accountId, $token);
      } catch (Throwable) {
        return false;
      }
    }



    public function getUser(): AccountEntity|null {
      try {
        $token = Headers::getBearerToken();
        $payload = $this->jwt->decode($token);

        return $this->repository->findOneBy([
          'id' => $payload[AuthJWT::JWT_PAYLOAD_IDENTITY_KEY]
        ]);
      } catch (Throwable) {
        return null;
      }
    }



    public function isAllowed(string|Permission $right, $subject = null): bool {
      $account = $this->getUser();

      if ($account === null)
        return false;

      $role = $account->getRole();

      return match ($right) {
        Permission::ACCOUNT_MANAGEMENT => match ($role) {
          AccountRole::ADMIN,
          AccountRole::SYSTEM => true,
          default => false
        },
        Permission::ONLY_TRUSTED => match ($role) {
          AccountRole::SYSTEM => true,
          default => false
        },
        default => false
      };
    }



    /**
     * @throws EmailNotVerified
     * @throws AccountMarkedAsRemoved
     * @throws NotAuthenticated
     */
    protected function ensureTrustedIdentity(AccountEntity $account = null): void {
      $account ??= $this->getUser();

      if ($account === null)
        throw new NotAuthenticated();

      if ($account->getIsMarkedAsRemoved())
        throw new EmailNotVerified();

      if (!$account->getEmailVerified())
        throw new AccountMarkedAsRemoved();
    }



    /**
     * @throws EmailNotFound
     * @throws IncorrectPassword
     * @throws AccountMarkedAsRemoved
     * @throws NotAuthenticated
     * @throws EmailNotVerified
     */
    public function login(LoginInput $login): string {
      try {
        /** @var AccountEntity $account */
        $account = $this->repository->createQueryBuilder('a')
          ->select('a')
          ->where('a.email = :email')
          ->setParameter('email', $login->email)
          ->getQuery()
          ->getSingleResult();
      } catch (Exception) {
        throw new EmailNotFound();
      }

      $doPasswordsMatch = password_verify($login->password, $account->getPasswordHash());

      if (!$doPasswordsMatch)
        throw new IncorrectPassword();

      $this->ensureTrustedIdentity($account);

      return $this->authTokenManager->createAndAllowAuthToken($account->getId());
    }



    public function logout(): bool {
      try {
        $token = Headers::getBearerToken();
        $accountId = (int) $this->jwt->decode($token)[AuthJWT::JWT_PAYLOAD_IDENTITY_KEY];

        return $this->authTokenManager->disallowAuthToken($accountId, $token);
      } catch (Throwable) {
        return false;
      }
    }



    /**
     * @throws InvalidToken
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws TokenExpired
     */
    public function renewToken(): string {
      $token = Headers::getBearerToken();

      // TODO: check if not too young for renewal

      $accountId = (int) $this->jwt->decode($token)[AuthJWT::JWT_PAYLOAD_IDENTITY_KEY];
      $this->authTokenManager->disallowAuthToken($accountId, $token);

      return $this->authTokenManager->createAndAllowAuthToken($accountId);
    }
  }
}
