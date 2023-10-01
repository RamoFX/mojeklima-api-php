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
  use Doctrine\ORM\Exception\ORMException;
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
    public static function register(CreateAccountInput $account): string {
      $emails_count = EntityManagerProxy::$entity_manager->createQueryBuilder()
        ->select("count(account.id)")
        ->from(Account::class, "account")
        ->where("account.email = :email")
        ->setParameter("email", $account->email)
        ->getQuery()
        ->getSingleScalarResult();

      if ($emails_count > 0)
        throw new AccountAlreadyExist();

      $randomPassword = Random::randomString(6, "abc123");

      $new_account = new Account(AccountRoleEnum::USER(), $account->name, $account->email, $randomPassword);

      EntityManagerProxy::$entity_manager->persist($new_account);
      EntityManagerProxy::$entity_manager->flush($new_account);

      Email::sendPassword($account->email, $randomPassword);

      return "";
    }



    /** @Mutation() */
    public static function resetPassword(string $email): string {
      try {
        /* @var Account $account */
        $account = EntityManagerProxy::$entity_manager->createQueryBuilder()
          ->select("account")
          ->from(Account::class, "account")
          ->where("account.email = :email")
          ->setParameter("email", $email)
          ->getQuery()
          ->getSingleResult();

        $randomPassword = Random::randomString(6, "abc123");

        $account->setPassword($randomPassword);

        EntityManagerProxy::$entity_manager->persist($account);
        EntityManagerProxy::$entity_manager->flush($account);

        Email::sendPassword($email, $randomPassword);

        return "";
      } catch (NoResultException $exception) {
        throw new EmailNotFound();
      }
    }
  }
}
