<?php



namespace App\Resources\Auth {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Account\Exceptions\AccountAlreadyExist;
  use App\Resources\Account\Exceptions\AccountMarkedAsRemoved;
  use App\Resources\Account\Exceptions\EmailNotFound;
  use App\Resources\Account\InputTypes\CreateAccount;
  use App\Resources\Auth\Exceptions\IncorrectPassword;
  use App\Resources\Auth\Utilities\JWT;
  use App\Resources\Common\Utilities\GlobalProxy;
  use App\Resources\Common\Utilities\Random;
  use App\Resources\Email\EmailService;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\NoResultException;
  use Doctrine\ORM\OptimisticLockException;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class AuthController {
    /**
     * @throws IncorrectPassword
     * @throws EmailNotFound
     * @throws AccountMarkedAsRemoved
     */
    #[Mutation]
    public static function login(string $email, string $password, bool $remember): string {
      try {
        /** @var $account AccountEntity */
        $account = GlobalProxy::$entityManager->createQueryBuilder()
          ->select("account")
          ->from(AccountEntity::class, "account")
          ->where("account.email = :email")
          ->setParameter("email", $email)
          ->getQuery()
          ->getSingleResult();
      } catch (Exception $exception) {
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
     * @throws GraphQLException
     * @throws ORMException
     * @throws Exception
     */
    #[Mutation]
    public static function register(CreateAccount $account): bool {
      $emails_count = GlobalProxy::$entityManager->createQueryBuilder()
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

      GlobalProxy::$entityManager->persist($new_account);
      GlobalProxy::$entityManager->flush($new_account);

      return EmailService::sendPassword($account->email, $random_password);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws NonUniqueResultException
     * @throws EmailNotFound
     * @throws AccountMarkedAsRemoved
     */
    #[Mutation]
    public static function resetPassword(string $email): bool {
      try {
        /* @var AccountEntity $account */
        $account = GlobalProxy::$entityManager->createQueryBuilder()
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

        GlobalProxy::$entityManager->persist($account);
        GlobalProxy::$entityManager->flush($account);

        return EmailService::sendPassword($email, $random_password);
      } catch (NoResultException) {
        throw new EmailNotFound();
      }
    }
  }
}
