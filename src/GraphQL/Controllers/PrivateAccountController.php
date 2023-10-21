<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\EntityManagerProxy;
  use App\Core\Enums\AccountRole;
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
    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function me(Account $current_account): Account {
      return $current_account;
    }



    /**
     * @Query()
     * @Logged()
     * @Right("CAN_ACCESS_USERS")
     */
    public static function account(int $id): Account {
      try {
        return EntityManagerProxy::$entity_manager->find(Account::class, $id);
      } catch (Exception $exception) {
        throw new EntityNotFound("Account");
      }
    }



    /**
     * @Query()
     * @Logged()
     * @Right("CAN_ACCESS_USERS")
     * @return Account[]
     */
    public static function accounts(): array {
      /** @var Account[] $accounts */
      $accounts = EntityManagerProxy::$entity_manager->getRepository(Account::class)->findAll();
      return $accounts;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function renewToken(Account $currentAccount, bool $remember): string {
      return JWTService::renewToken($currentAccount->getId(), $remember);
    }



    /**
     * @Mutation()
     * @Logged()
     * @Right("CAN_CHANGE_ROLE")
     */
      /** @var $account Account */
    public static function changeRole(int $id, AccountRole $role): Account {
      $account = EntityManagerProxy::$entity_manager->find(Account::class, $id);

      if ($account === null)
        throw new EntityNotFound("Account");

      $account->setRole($role);

      EntityManagerProxy::$entity_manager->flush($account);

      return $account;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function updateName(Account $current_account, string $name): Account {
      // update
      $current_account->setName($name);

      // save
      EntityManagerProxy::$entity_manager->persist($current_account);
      EntityManagerProxy::$entity_manager->flush($current_account);

      return $current_account;
    }

    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function updateAvatar(#[InjectUser] Account $currentAccount): Account {
      return $currentAccount;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function updateEmail(Account $current_account, string $email): Account {
      // check whether email is already in use
      $emails_count = EntityManagerProxy::$entity_manager->createQueryBuilder()
        ->select("count(account.id)")
        ->from(Account::class, "account")
        ->where("account.email = :email")
        ->setParameter("email", $email)
        ->getQuery()
        ->getSingleScalarResult();

      if ($emails_count > 0)
        throw new EmailAlreadyInUse();

      // update
      $current_account->setEmail($email);

      // save
      EntityManagerProxy::$entity_manager->persist($current_account);
      EntityManagerProxy::$entity_manager->flush($current_account);

      return $current_account;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function updatePassword(Account $current_account, string $password): Account {
      // update
      $current_account->setPassword($password);

      // save
      EntityManagerProxy::$entity_manager->persist($current_account);
      EntityManagerProxy::$entity_manager->flush($current_account);

      return $current_account;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function deleteAccount(Account $current_account): Account {
      EntityManagerProxy::$entity_manager->remove($current_account);
      EntityManagerProxy::$entity_manager->flush($current_account);

      return $current_account;
    }
  }
}
