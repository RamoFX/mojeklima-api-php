<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\EntityManagerProxy;
  use App\Core\Enums\AccountRoleEnum;
  use App\GraphQL\Exceptions\EmailAlreadyInUse;
  use App\GraphQL\Exceptions\EntityNotFound;
  use App\GraphQL\Services\JWTService;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Annotations\Right;



  class PrivateAccountController {
    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function me(Account $currentAccount): Account {
      return $currentAccount;
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
    public static function changeRole(int $id, AccountRoleEnum $role): Account {
      /** @var $account Account */
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
     * @InjectUser(for="$currentAccount")
     */
    public static function updateName(Account $currentAccount, string $name): Account {
      // update
      $currentAccount->setName($name);

      // save
      EntityManagerProxy::$entity_manager->persist($currentAccount);
      EntityManagerProxy::$entity_manager->flush($currentAccount);

      return $currentAccount;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function updateAvatar(Account $currentAccount): Account {
//      // check whether avatar present
//      if (!isset($_FILES["avatar"]))
//        throw new FileMissing();
//
//      // get avatar
//      $avatar = $_FILES["avatar"];
//
//      // check size
//      $maxSize = 5_000_000; // 5 MB
//
//      if ($avatar['size'] > $maxSize)
//        throw new FileTooBig($maxSize);
//
//      // check type
//      $allowedTypes = [ 'apng', 'avif', 'jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'png', 'webp' ]; // https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types
//      $type = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION)) ?? "unknown";
//
//      if (!in_array($type, $allowedTypes))
//        throw new FileTypeUnsupported($type, $allowedTypes);
//
//      // save avatar
//      $id = $currentAccount->getId();
//      $finalPath = realpath(__DIR__ . "/../../../uploads/avatars/$id.$type");
//      move_uploaded_file($avatar["tmp_name"], $finalPath);
//
//      // save avatar_url
//      $currentAccount->setAvatarUrl($avatar);
//
//      // save
//      EntityManagerProxy::$entity_manager->persist($currentAccount);
//      EntityManagerProxy::$entity_manager->flush($currentAccount);

      return $currentAccount;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function updateEmail(Account $currentAccount, string $email): Account {
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
      $currentAccount->setEmail($email);

      // save
      EntityManagerProxy::$entity_manager->persist($currentAccount);
      EntityManagerProxy::$entity_manager->flush($currentAccount);

      return $currentAccount;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function updatePassword(Account $currentAccount, string $password): Account {
      // update
      $currentAccount->setPassword($password);

      // save
      EntityManagerProxy::$entity_manager->persist($currentAccount);
      EntityManagerProxy::$entity_manager->flush($currentAccount);

      return $currentAccount;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function deleteAccount(Account $currentAccount): string {
      EntityManagerProxy::$entity_manager->remove($currentAccount);
      EntityManagerProxy::$entity_manager->flush($currentAccount);

      return "ok";
    }
  }
}
