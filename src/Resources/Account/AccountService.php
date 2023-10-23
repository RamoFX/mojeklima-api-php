<?php



namespace App\Resources\Account {

  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Account\Exceptions\EmailAlreadyInUse;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Common\Utilities\GlobalProxy;
  use Doctrine\ORM\Exception\NotSupported;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\NoResultException;
  use Doctrine\ORM\OptimisticLockException;
  use Doctrine\ORM\TransactionRequiredException;
  use Exception;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class AccountService {
    /**
     * @throws EntityNotFound
     */
    public function account(int $id) {
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
    public function accounts(): array {
      return GlobalProxy::$entityManager->getRepository(AccountEntity::class)->findAll();
    }



    public function accountsCount(): int {
      // TODO: Implement

      return 0;
    }



    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws ORMException
     * @throws TransactionRequiredException
     */
    public function changeRole(int $id, AccountRole $role): AccountEntity {
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
    public function updateName(AccountEntity $currentAccount, string $name): AccountEntity {
      $currentAccount->setName($name);

      GlobalProxy::$entityManager->persist($currentAccount);
      GlobalProxy::$entityManager->flush($currentAccount);

      return $currentAccount;
    }



    public function updateAvatar(AccountEntity $currentAccount): AccountEntity {
      // TODO: Implement

      return $currentAccount;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws EmailAlreadyInUse
     * @throws GraphQLException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function updateEmail(AccountEntity $currentAccount, string $email): AccountEntity {
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
    public function updatePassword(AccountEntity $currentAccount, string $password): AccountEntity {
      $currentAccount->setPassword($password);

      GlobalProxy::$entityManager->persist($currentAccount);
      GlobalProxy::$entityManager->flush($currentAccount);

      return $currentAccount;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function markAccountRemoved(AccountEntity $currentAccount): AccountEntity {
      $currentAccount->setIsMarkedAsRemoved(true);

      GlobalProxy::$entityManager->persist($currentAccount);
      GlobalProxy::$entityManager->flush($currentAccount);

      return $currentAccount;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function permanentlyDeleteAccount(AccountEntity $currentAccount): AccountEntity {
      GlobalProxy::$entityManager->remove($currentAccount);
      GlobalProxy::$entityManager->flush($currentAccount);

      return $currentAccount;
    }
  }
}
