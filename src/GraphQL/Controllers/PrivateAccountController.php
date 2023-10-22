<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Enums\AccountRole;
  use App\GlobalProxy;
  use App\GraphQL\Exceptions\AuthorizationHeaderMissing;
  use App\GraphQL\Exceptions\BearerTokenMissing;
  use App\GraphQL\Exceptions\EmailAlreadyInUse;
  use App\GraphQL\Exceptions\EntityNotFound;
  use App\GraphQL\Exceptions\InvalidToken;
  use App\GraphQL\Exceptions\TokenExpired;
  use App\GraphQL\Services\JWTService;
  use Doctrine\ORM\Exception\NotSupported;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\NoResultException;
  use Doctrine\ORM\OptimisticLockException;
  use Doctrine\ORM\TransactionRequiredException;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Annotations\Right;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class PrivateAccountController {
    #[Query]
    #[Logged]
    public static function me(#[InjectUser] Account $currentAccount): Account {
      return $currentAccount;
    }

    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    #[Right("ACCOUNT_MANAGEMENT")]
    public static function account(int $id): Account {
      try {
        return GlobalProxy::$entityManager->find(Account::class, $id);
      } catch (Exception) {
        throw new EntityNotFound("Account");
      }
    }

    /**
     * @return Account[]
     * @throws NotSupported
     */
    #[Query]
    #[Logged]
    #[Right("ACCOUNT_MANAGEMENT")]
    public static function accounts(): array {
      return GlobalProxy::$entityManager->getRepository(Account::class)->findAll();
    }

    /**
     * @throws InvalidToken
     * @throws AuthorizationHeaderMissing
     * @throws TokenExpired
     * @throws BearerTokenMissing
     */
    #[Mutation]
    #[Logged]
    public static function renewToken(#[InjectUser] Account $currentAccount, bool $remember): string {
      return JWTService::renewToken($currentAccount->getId(), $remember);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws EntityNotFound
     * @throws TransactionRequiredException
     */
    #[Mutation]
    #[Logged]
    #[Right("ACCOUNT_MANAGEMENT")]
    public static function changeRole(int $id, AccountRole $role): Account {
      $account = GlobalProxy::$entityManager->find(Account::class, $id);

      if ($account === null)
        throw new EntityNotFound("Account");

      $account->setRole($role);

      GlobalProxy::$entityManager->flush($account);

      return $account;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     */
    #[Mutation]
    #[Logged]
    public static function updateName(#[InjectUser] Account $currentAccount, string $name): Account {
      $currentAccount->setName($name);

      GlobalProxy::$entityManager->persist($currentAccount);
      GlobalProxy::$entityManager->flush($currentAccount);

      return $currentAccount;
    }

    #[Mutation]
    #[Logged]
    public static function updateAvatar(#[InjectUser] Account $currentAccount): Account {
      return $currentAccount;
    }

    /**
     * @throws OptimisticLockException
     * @throws EmailAlreadyInUse
     * @throws GraphQLException
     * @throws ORMException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Mutation]
    #[Logged]
    public static function updateEmail(#[InjectUser] Account $currentAccount, string $email): Account {
      // check whether email is already in use
      $emails_count = GlobalProxy::$entityManager->createQueryBuilder()
        ->select("count(account.id)")
        ->from(Account::class, "account")
        ->where("account.email = :email")
        ->setParameter("email", $email)
        ->getQuery()
        ->getSingleScalarResult();

      if ($emails_count > 0)
        throw new EmailAlreadyInUse();

      $currentAccount->setEmail($email);

      GlobalProxy::$entityManager->persist($currentAccount);
      GlobalProxy::$entityManager->flush($currentAccount);

      return $currentAccount;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public static function updatePassword(#[InjectUser] Account $currentAccount, string $password): Account {
      $currentAccount->setPassword($password);

      GlobalProxy::$entityManager->persist($currentAccount);
      GlobalProxy::$entityManager->flush($currentAccount);

      return $currentAccount;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public static function markAccountRemoved(#[InjectUser] Account $currentAccount): Account {
      $currentAccount->setIsMarkedAsRemoved(true);

      GlobalProxy::$entityManager->persist($currentAccount);
      GlobalProxy::$entityManager->flush($currentAccount);

      return $currentAccount;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    #[Right('ACCOUNT_MANAGEMENT')]
    public static function permanentlyDeleteAccount(#[InjectUser] Account $currentAccount): Account {
      GlobalProxy::$entityManager->remove($currentAccount);
      GlobalProxy::$entityManager->flush($currentAccount);

      return $currentAccount;
    }
  }
}
