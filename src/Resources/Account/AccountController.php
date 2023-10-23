<?php



namespace App\Resources\Account {

  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Account\Exceptions\EmailAlreadyInUse;
  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Auth\Utilities\JWT;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Common\Utilities\GlobalProxy;
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



  class AccountController {
    #[Query]
    #[Logged]
    public static function me(#[InjectUser] AccountEntity $currentAccount): AccountEntity {
      return $currentAccount;
    }



    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    #[Right("ACCOUNT_MANAGEMENT")]
    public static function account(int $id): AccountEntity {
      try {
        return GlobalProxy::$entityManager->find(AccountEntity::class, $id);
      } catch (Exception) {
        throw new EntityNotFound("Account");
      }
    }



    /**
     * @return AccountEntity[]
     * @throws NotSupported
     */
    #[Query]
    #[Logged]
    #[Right("ACCOUNT_MANAGEMENT")]
    public static function accounts(): array {
      return GlobalProxy::$entityManager->getRepository(AccountEntity::class)->findAll();
    }



    /**
     * @throws InvalidToken
     * @throws AuthorizationHeaderMissing
     * @throws TokenExpired
     * @throws BearerTokenMissing
     */
    #[Mutation]
    #[Logged]
    public static function renewToken(#[InjectUser] AccountEntity $currentAccount, bool $remember): string {
      return JWT::renewToken($currentAccount->getId(), $remember);
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
    public static function changeRole(int $id, AccountRole $role): AccountEntity {
      $account = GlobalProxy::$entityManager->find(AccountEntity::class, $id);

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
    public static function updateName(#[InjectUser] AccountEntity $currentAccount, string $name): AccountEntity {
      $currentAccount->setName($name);

      GlobalProxy::$entityManager->persist($currentAccount);
      GlobalProxy::$entityManager->flush($currentAccount);

      return $currentAccount;
    }



    #[Mutation]
    #[Logged]
    public static function updateAvatar(#[InjectUser] AccountEntity $currentAccount): AccountEntity {
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
    public static function updateEmail(#[InjectUser] AccountEntity $currentAccount, string $email): AccountEntity {
      // check whether email is already in use
      $emails_count = GlobalProxy::$entityManager->createQueryBuilder()
        ->select("count(account.id)")
        ->from(AccountEntity::class, "account")
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
    public static function updatePassword(#[InjectUser] AccountEntity $currentAccount, string $password): AccountEntity {
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
    public static function markAccountRemoved(#[InjectUser] AccountEntity $currentAccount): AccountEntity {
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
    public static function permanentlyDeleteAccount(#[InjectUser] AccountEntity $currentAccount): AccountEntity {
      GlobalProxy::$entityManager->remove($currentAccount);
      GlobalProxy::$entityManager->flush($currentAccount);

      return $currentAccount;
    }
  }
}
