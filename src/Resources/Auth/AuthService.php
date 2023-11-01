<?php



namespace App\Resources\Auth {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Account\Exceptions\AccountAlreadyExist;
  use App\Resources\Account\Exceptions\AccountMarkedAsRemoved;
  use App\Resources\Account\Exceptions\EmailNotFound;
  use App\Resources\Account\InputTypes\CreateAccount;
  use App\Resources\Alert\AlertEntity;
  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\IncorrectPassword;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Auth\Utilities\JWT;
  use App\Resources\Common\Utilities\Headers;
  use App\Resources\Common\Utilities\Random;
  use App\Resources\Email\EmailService;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\EntityRepository;
  use Doctrine\ORM\Exception\NotSupported;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\NoResultException;
  use Doctrine\ORM\OptimisticLockException;
  use Doctrine\ORM\TransactionRequiredException;
  use Exception;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;
  use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
  use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;



  class AuthService implements AuthenticationServiceInterface, AuthorizationServiceInterface {
    protected EntityRepository $repository;



    /**
     * @throws NotSupported
     */
    public function __construct(
      protected EntityManager $entityManager,
      protected EmailService $emailService
    ) {
      $this->repository = $entityManager->getRepository(AlertEntity::class);
    }



    /**
     * @throws BearerTokenMissing
     * @throws AuthorizationHeaderMissing
     */
    public static function getBearerToken(): string {
      $authorization = Headers::get("Authorization");

      if ($authorization === null)
        throw new AuthorizationHeaderMissing();

      $isBearer = str_starts_with($authorization, "Bearer");

      if (!$isBearer)
        throw new BearerTokenMissing();

      return substr($authorization, strlen("Bearer "));
    }



    /**
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws InvalidToken
     * @throws TokenExpired
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function getUser(): ?AccountEntity {
      $headerToken = self::getBearerToken();
      $tokenDecoded = JWT::decodeToken($headerToken);

      return $this->entityManager->find(AccountEntity::class, $tokenDecoded["id"]);
    }



    public function isLogged(): bool {
      try {
        return $this->getUser() !== null;
      } catch (Exception) {
        return false;
      }
    }



    /**
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function isAllowed(string $right, $subject = null): bool {
      $account = $this->getUser();

      if ($account === null)
        return false;

      return match ($right) {
        "ACCOUNT_MANAGEMENT" => $this->isAdminAccount() || $this->isSystemAccount(),
        "PUSH_NOTIFICATIONS" => $this->isSystemAccount(),
        default => false
      };
    }



    /**
     * @throws AuthorizationHeaderMissing
     * @throws ORMException
     * @throws BearerTokenMissing
     * @throws TokenExpired
     * @throws InvalidToken
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    private function isAdminAccount(): bool {
      $account = $this->getUser();

      return $account->getRole() === AccountRole::ADMIN;
    }



    /**
     * @throws AuthorizationHeaderMissing
     * @throws ORMException
     * @throws BearerTokenMissing
     * @throws TokenExpired
     * @throws OptimisticLockException
     * @throws InvalidToken
     * @throws TransactionRequiredException
     */
    private function isSystemAccount(): bool {
      $account = $this->getUser();

      return $account->getRole() === AccountRole::SYSTEM;
    }



    /**
     * @throws EmailNotFound
     * @throws IncorrectPassword
     * @throws AccountMarkedAsRemoved
     */
    public function login(string $email, string $password, bool $remember): string {
      try {
        /** @var $account AccountEntity */
        $account = $this->entityManager->createQueryBuilder()
          ->select("account")
          ->from(AccountEntity::class, "account")
          ->where("account.email = :email")
          ->setParameter("email", $email)
          ->getQuery()
          ->getSingleResult();
      } catch (Exception) {
        throw new EmailNotFound();
      }

      if ($account->getIsMarkedAsRemoved())
        throw new AccountMarkedAsRemoved();

      $do_passwords_match = password_verify($password, $account->getPasswordHash());

      if (!$do_passwords_match)
        throw new IncorrectPassword();

      return JWT::createToken($account->getId(), $remember);
    }



    /**
     * @throws OptimisticLockException
     * @throws GraphQLException
     * @throws ORMException
     * @throws NonUniqueResultException
     * @throws AccountAlreadyExist
     * @throws NoResultException
     * @throws Exception
     */
    public function register(CreateAccount $account): bool {
      $emails_count = $this->entityManager->createQueryBuilder()
        ->select("count(account.id)")
        ->from(AccountEntity::class, "account")
        ->where("account.email = :email")
        ->setParameter("email", $account->email)
        ->getQuery()
        ->getSingleScalarResult();

      if ($emails_count > 0)
        throw new AccountAlreadyExist();

      $random_password = Random::randomString(6, "abc123");

      $new_account = new AccountEntity(AccountRole::USER, $account->name, $account->email, $random_password);

      $this->entityManager->persist($new_account);
      $this->entityManager->flush($new_account);

      return $this->emailService->sendPassword($account->email, $random_password);
    }



    /**
     * @throws OptimisticLockException
     * @throws EmailNotFound
     * @throws ORMException
     * @throws AccountMarkedAsRemoved
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function resetPassword(string $email): bool {
      try {
        /* @var AccountEntity $account */
        $account = $this->entityManager->createQueryBuilder()
          ->select("account")
          ->from(AccountEntity::class, "account")
          ->where("account.email = :email")
          ->setParameter("email", $email)
          ->getQuery()
          ->getSingleResult();

        if ($account->getIsMarkedAsRemoved())
          throw new AccountMarkedAsRemoved();

        $random_password = Random::randomString(6, "abc123");

        $account->setPassword($random_password);

        $this->entityManager->persist($account);
        $this->entityManager->flush($account);

        return $this->emailService->sendPassword($email, $random_password);
      } catch (NoResultException) {
        throw new EmailNotFound();
      }
    }



    /**
     * @throws InvalidToken
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws TokenExpired
     */
    public function renewToken(AccountEntity $currentAccount, bool $remember): string {
      return JWT::renewToken($currentAccount->getId(), $remember);
    }
  }
}
