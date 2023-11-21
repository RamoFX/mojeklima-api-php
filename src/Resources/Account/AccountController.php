<?php



namespace App\Resources\Account {

  use App\Resources\Account\DTO\AccountInput;
  use App\Resources\Account\DTO\BeginAccountRemovalInput;
  use App\Resources\Account\DTO\BeginEmailVerificationInput;
  use App\Resources\Account\DTO\BeginPasswordResetInput;
  use App\Resources\Account\DTO\ChangeRoleInput;
  use App\Resources\Account\DTO\CompleteAccountRemovalInput;
  use App\Resources\Account\DTO\CompleteEmailVerificationInput;
  use App\Resources\Account\DTO\CompletePasswordResetInput;
  use App\Resources\Account\DTO\CreateAccountInput;
  use App\Resources\Account\DTO\UpdateAccountInput;
  use App\Resources\Account\DTO\UploadAvatarInput;
  use App\Resources\Account\Exceptions\AccountAlreadyExist;
  use App\Resources\Account\Exceptions\AccountMarkedAsRemoved;
  use App\Resources\Account\Exceptions\EmailAlreadyVerified;
  use App\Resources\Account\Exceptions\EmailNotFound;
  use App\Resources\Account\Exceptions\MustBeMarkedAsRemovedFirst;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Permission\Enums\Permission;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\NoResultException;
  use Doctrine\ORM\OptimisticLockException;
  use Doctrine\ORM\TransactionRequiredException;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Annotations\Right;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  readonly class AccountController {
    public function __construct(
      protected AccountService $accountService
    ) {}



    #[Query]
    #[Logged]
    public function me(): AccountEntity {
      return $this->accountService->me();
    }



    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    #[Right(Permission::ACCOUNT_MANAGEMENT)]
    public function account(AccountInput $account): AccountEntity {
      return $this->accountService->account($account);
    }



    /**
     * @return AccountEntity[]
     */
    #[Query]
    #[Logged]
    #[Right(Permission::ACCOUNT_MANAGEMENT)]
    public function accounts(): array {
      return $this->accountService->accounts();
    }



    /**
     * @return AccountEntity[]
     */
    #[Query]
    #[Logged]
    #[Right(Permission::ACCOUNT_MANAGEMENT)]
    public function userAccounts(): array {
      return $this->accountService->userAccounts();
    }



    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Query]
    #[Logged]
    #[Right(Permission::ACCOUNT_MANAGEMENT)]
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
    #[Right(Permission::ACCOUNT_MANAGEMENT)]
    public function changeRole(ChangeRoleInput $changeRole): AccountEntity {
      return $this->accountService->changeRole($changeRole);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     * @throws NonUniqueResultException
     * @throws NoResultException
     * @throws AccountAlreadyExist
     */
    #[Mutation]
    public function createAccount(CreateAccountInput $createAccount): AccountEntity {
      return $this->accountService->createAccount($createAccount);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     */
    #[Mutation]
    #[Logged]
    public function updateAccount(UpdateAccountInput $updateAccount): AccountEntity {
      return $this->accountService->updateAccount($updateAccount);
    }



    /**
     * @throws EmailNotFound
     * @throws EmailAlreadyVerified
     */
    #[Mutation]
    public function beginEmailVerification(BeginEmailVerificationInput $beginEmailVerification): bool {
      return $this->accountService->beginEmailVerification($beginEmailVerification);
    }



    /**
     * @throws EmailAlreadyVerified
     * @throws EmailNotFound
     * @throws InvalidToken
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TokenExpired
     */
    #[Mutation]
    public function completeEmailVerification(CompleteEmailVerificationInput $completeEmailVerification): bool {
      return $this->accountService->completeEmailVerification($completeEmailVerification);
    }



    /**
     * @throws EmailNotFound
     * @throws AccountMarkedAsRemoved
     */
    #[Mutation]
    public function beginPasswordReset(BeginPasswordResetInput $beginPasswordReset): bool {
      return $this->accountService->beginPasswordReset($beginPasswordReset);
    }



    /**
     * @throws OptimisticLockException
     * @throws EmailNotFound
     * @throws InvalidToken
     * @throws ORMException
     * @throws TokenExpired
     */
    #[Mutation]
    public function completePasswordReset(CompletePasswordResetInput $completePasswordReset): bool {
      return $this->accountService->completePasswordReset($completePasswordReset);
    }



    #[Mutation]
    #[Logged]
    public function uploadAvatar(UploadAvatarInput $uploadAvatar): AccountEntity {
      return $this->accountService->uploadAvatar($uploadAvatar);
    }



    /**
     * @throws EmailNotFound
     */
    #[Mutation]
    #[Logged]
    public function beginAccountRemoval(BeginAccountRemovalInput $beginAccountRemoval): bool {
      return $this->accountService->beginAccountRemoval($beginAccountRemoval);
    }



    /**
     * @throws EmailNotFound
     * @throws InvalidToken
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws TokenExpired
     */
    #[Mutation]
    #[Logged]
    public function completeAccountRemoval(CompleteAccountRemovalInput $completeAccountRemoval): bool {
      return $this->accountService->completeAccountRemoval($completeAccountRemoval);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws MustBeMarkedAsRemovedFirst
     */
    #[Mutation]
    #[Logged]
    #[Right(Permission::ACCOUNT_MANAGEMENT)]
    public function permanentlyDeleteAccount(): AccountEntity {
      return $this->accountService->permanentlyDeleteAccount();
    }
  }
}
