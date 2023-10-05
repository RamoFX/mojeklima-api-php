<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\EntityManagerProxy;
  use App\Core\Enums\AccountRoleEnum;
  use App\GraphQL\Exceptions\AccountAlreadyExist;
  use App\GraphQL\Exceptions\EmailNotFound;
  use App\GraphQL\Exceptions\IncorrectPassword;
  use App\GraphQL\InputTypes\CreateAccountInput;
  use App\GraphQL\Services\JWTService;
  use App\Utilities\Email;
  use App\Utilities\Random;
  use Doctrine\ORM\NoResultException;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;



  class PublicAccountController {
    /** @Mutation() */
    public static function login(string $email, string $password, bool $remember): string {
      try {
        /** @var $account Account */
        $account = EntityManagerProxy::$entity_manager->createQueryBuilder()
          ->select("account")
          ->from(Account::class, "account")
          ->where("account.email = :email")
          ->setParameter("email", $email)
          ->getQuery()
          ->getSingleResult();
      } catch (Exception $exception) {
        throw new EmailNotFound();
      }

      $do_passwords_match = password_verify($password, $account->getPasswordHash());

      if (!$do_passwords_match)
        throw new IncorrectPassword();

      return JWTService::createToken($account->getId(), $remember);
    }



    /** @Mutation() */
    public static function register(CreateAccountInput $account): bool {
      $emails_count = EntityManagerProxy::$entity_manager->createQueryBuilder()
        ->select("count(account.id)")
        ->from(Account::class, "account")
        ->where("account.email = :email")
        ->setParameter("email", $account->email)
        ->getQuery()
        ->getSingleScalarResult();

      if ($emails_count > 0)
        throw new AccountAlreadyExist();

      $random_password = Random::randomString(6, "abc123");

      $new_account = new Account(AccountRoleEnum::USER(), $account->name, $account->email, $random_password);

      EntityManagerProxy::$entity_manager->persist($new_account);
      EntityManagerProxy::$entity_manager->flush($new_account);

      return Email::send_password($account->email, $random_password);
    }



    /** @Mutation() */
    public static function resetPassword(string $email): bool {
      try {
        /* @var Account $account */
        $account = EntityManagerProxy::$entity_manager->createQueryBuilder()
          ->select("account")
          ->from(Account::class, "account")
          ->where("account.email = :email")
          ->setParameter("email", $email)
          ->getQuery()
          ->getSingleResult();

        $random_password = Random::randomString(6, "abc123");

        $account->setPassword($random_password);

        EntityManagerProxy::$entity_manager->persist($account);
        EntityManagerProxy::$entity_manager->flush($account);

        return Email::send_password($email, $random_password);
      } catch (NoResultException $exception) {
        throw new EmailNotFound();
      }
    }
  }
}
