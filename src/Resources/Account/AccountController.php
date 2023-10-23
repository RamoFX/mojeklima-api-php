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
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Annotations\Right;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  readonly class AccountController {
    private AccountService $accountService;



    /**
     * @throws Exception
     */
    public function __construct() {
      $this->accountService = GlobalProxy::$container->get(AccountService::class);
    }



    #[Query]
    #[Logged]
    public function me(
      #[InjectUser] AccountEntity $currentAccount
    ): AccountEntity {
      return $currentAccount;
    }



    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    #[Right("ACCOUNT_MANAGEMENT")]
    public function account(
      int $id
    ): AccountEntity {
      return $this->accountService->account($id);
    }



    /**
     * @return AccountEntity[]
     * @throws NotSupported
     */
    #[Query]
    #[Logged]
    #[Right("ACCOUNT_MANAGEMENT")]
    public function accounts(): array {
      return $this->accountService->accounts();
    }



    #[Query]
    #[Logged]
    #[Right("ACCOUNT_MANAGEMENT")]
    public function accountsCount(): int {
      return $this->accountService->accountsCount();
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
    public function changeRole(
      int $id,
      AccountRole $role
    ): AccountEntity {
      return $this->accountService->changeRole($id, $role);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     */
    #[Mutation]
    #[Logged]
    public function updateName(
      #[InjectUser] AccountEntity $currentAccount,
      string $name
    ): AccountEntity {
      return $this->accountService->updateName($currentAccount, $name);
    }



    #[Mutation]
    #[Logged]
    public function updateAvatar(
      #[InjectUser] AccountEntity $currentAccount
    ): AccountEntity {
      return $this->accountService->updateAvatar($currentAccount);
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
    public function updateEmail(
      #[InjectUser] AccountEntity $currentAccount,
      string $email
    ): AccountEntity {
      return $this->accountService->updateEmail($currentAccount, $email);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public function updatePassword(
      #[InjectUser] AccountEntity $currentAccount,
      string $password
    ): AccountEntity {
      return $this->accountService->updatePassword($currentAccount, $password);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public function markAccountRemoved(
      #[InjectUser] AccountEntity $currentAccount
    ): AccountEntity {
      return $this->accountService->markAccountRemoved($currentAccount);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    #[Right('ACCOUNT_MANAGEMENT')]
    public function permanentlyDeleteAccount(
      #[InjectUser] AccountEntity $currentAccount
    ): AccountEntity {
      return $this->accountService->permanentlyDeleteAccount($currentAccount);
    }
  }
}
