<?php



namespace App\Resources\Account {

  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Account\Exceptions\EmailAlreadyInUse;
  use App\Resources\Common\Exceptions\EntityNotFound;
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



  class AccountService {
    protected EntityRepository $repository;



    /**
     * @throws NotSupported
     */
    public function __construct(
      protected EntityManager $entityManager
    ) {
      $this->repository = $entityManager->getRepository(AccountEntity::class);
    }



    /**
     * @throws EntityNotFound
     */
    public function account(int $id) {
      try {
        return $this->repository->find(AccountEntity::class, $id);
      } catch (Exception) {
        throw new EntityNotFound("Account");
      }
    }



    /**
     * @return AccountEntity[]
     */
    public function accounts(): array {
      return $this->repository->findAll();
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
      $account = $this->repository->find(AccountEntity::class, $id);

      if ($account === null)
        throw new EntityNotFound("Account");

      $account->setRole($role);

      $this->entityManager->flush($account);

      return $account;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     */
    public function updateName(AccountEntity $currentAccount, string $name): AccountEntity {
      $currentAccount->setName($name);

      $this->entityManager->persist($currentAccount);
      $this->entityManager->flush($currentAccount);

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
      $emailsCount = $this->entityManager->createQueryBuilder()
        ->select("count(account.id)")
        ->from(AccountEntity::class, "account")
        ->where("account.email = :email")
        ->setParameter("email", $email)
        ->getQuery()
        ->getSingleScalarResult();

      if ($emailsCount > 0)
        throw new EmailAlreadyInUse();

      $currentAccount->setEmail($email);

      $this->entityManager->persist($currentAccount);
      $this->entityManager->flush($currentAccount);

      return $currentAccount;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function updatePassword(AccountEntity $currentAccount, string $password): AccountEntity {
      $currentAccount->setPassword($password);

      $this->entityManager->persist($currentAccount);
      $this->entityManager->flush($currentAccount);

      return $currentAccount;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function markAccountRemoved(AccountEntity $currentAccount): AccountEntity {
      $currentAccount->setIsMarkedAsRemoved(true);

      $this->entityManager->persist($currentAccount);
      $this->entityManager->flush($currentAccount);

      return $currentAccount;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function permanentlyDeleteAccount(AccountEntity $currentAccount): AccountEntity {
      $this->entityManager->remove($currentAccount);
      $this->entityManager->flush($currentAccount);

      return $currentAccount;
    }
  }
}
