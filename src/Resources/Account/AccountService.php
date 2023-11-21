<?php



namespace App\Resources\Account {

  use App\Resources\Account\DTO\AccountInput;
  use App\Resources\Account\DTO\BeginEmailVerificationInput;
  use App\Resources\Account\DTO\BeginPasswordResetInput;
  use App\Resources\Account\DTO\ChangeRoleInput;
  use App\Resources\Account\DTO\CompleteAccountRemovalInput;
  use App\Resources\Account\DTO\CompleteEmailVerificationInput;
  use App\Resources\Account\DTO\CompletePasswordResetInput;
  use App\Resources\Account\DTO\CreateAccountInput;
  use App\Resources\Account\DTO\UpdateAccountInput;
  use App\Resources\Account\DTO\UploadAvatarInput;
  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Account\Exceptions\AccountAlreadyExist;
  use App\Resources\Account\Exceptions\EmailAlreadyInUse;
  use App\Resources\Account\Exceptions\EmailAlreadyVerified;
  use App\Resources\Account\Exceptions\EmailNotFound;
  use App\Resources\Account\Exceptions\MustBeMarkedAsRemovedFirst;
  use App\Resources\Auth\AuthService;
  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Auth\Utilities\JWT;
  use App\Resources\Common\CommonService;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Email\EmailService;
  use DI\DependencyException;
  use DI\NotFoundException;
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



  class AccountService extends CommonService {
    protected EntityRepository $repository;



    /**
     * @throws NotSupported
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(
      protected EntityManager $entityManager,
      protected EmailService $emailService,
      protected JWT $jwt,
      protected AuthService $authService
    ) {
      parent::__construct();
      $this->repository = $entityManager->getRepository(AccountEntity::class);
    }



    public function me(): AccountEntity {
      return $this->currentAccount;
    }



    /**
     * @throws EntityNotFound
     */
    public function account(AccountInput $account) {
      try {
        return $this->repository->find(AccountEntity::class, $account->id);
      } catch (Exception) {
        throw new EntityNotFound('Account');
      }
    }



    /**
     * @return AccountEntity[]
     */
    public function accounts(): array {
      return $this->repository->findAll();
    }



    /**
     * @return AccountEntity[]
     */
    public function userAccounts(): array {
      return $this->repository->createQueryBuilder('a')
        ->select('a')
        ->where('a.role != :systemRole')
        ->setParameter('systemRole', AccountRole::SYSTEM)
        ->getQuery()
        ->getResult();
    }



    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function accountsCount(): int {
      return $this->repository->createQueryBuilder('a')
        ->select('COUNT(a.id)')
        ->getQuery()
        ->getSingleScalarResult();
    }



    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws ORMException
     * @throws TransactionRequiredException
     */
    public function changeRole(ChangeRoleInput $changeRole): AccountEntity {
      $account = $this->repository->find(AccountEntity::class, $changeRole->id);

      if ($account === null)
        throw new EntityNotFound('Account');

      $account->setRole($changeRole->role);

      $this->entityManager->flush($account);

      return $account;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     * @throws NonUniqueResultException
     * @throws AccountAlreadyExist
     * @throws NoResultException
     * @throws Exception
     */
    public function createAccount(CreateAccountInput $createAccount): AccountEntity {
      // check if account already exists
      $emailsCount = (int) $this->repository->createQueryBuilder('a')
        ->select('COUNT(a.id)')
        ->where('a.email = :email')
        ->setParameter('email', $createAccount->email)
        ->getQuery()
        ->getSingleScalarResult();

      if ($emailsCount > 0)
        throw new AccountAlreadyExist();

      $newAccount = new AccountEntity(
        AccountRole::USER,
        $createAccount->name,
        $createAccount->email,
        $createAccount->password
      );

      $this->entityManager->persist($newAccount);
      $this->entityManager->flush($newAccount);

      return $newAccount;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     */
    public function updateAccount(UpdateAccountInput $updateAccount): AccountEntity {
      // perform checks before making any updates to the account
      // is email already in use?
      $emailsCount = (int) $this->repository->createQueryBuilder('a')
        ->select('COUNT(a.id)')
        ->where('a.email = :email')
        ->setParameter('email', $updateAccount->email)
        ->getQuery()
        ->getSingleScalarResult();

      if ($emailsCount > 0)
        throw new EmailAlreadyInUse();

      if ($updateAccount->name !== null)
        $this->currentAccount->setName($updateAccount->name);

      if ($updateAccount->email !== null) {
        $this->currentAccount->setEmail($updateAccount->email);
        $this->currentAccount->setEmailVerified(false);
      }

      if ($updateAccount->password !== null)
        $this->currentAccount->setPassword($updateAccount->password);

      $this->entityManager->flush($this->currentAccount);

      return $this->currentAccount;
    }



    /**
     * @throws EmailNotFound
     * @throws EmailAlreadyVerified
     */
    public function beginEmailVerification(BeginEmailVerificationInput $beginEmailVerification): bool {
      try {
        /* @var AccountEntity $account */
        $account = $this->repository->createQueryBuilder('a')
          ->select('a')
          ->where('a.email = :email')
          ->setParameter('email', $beginEmailVerification->email)
          ->getQuery()
          ->getSingleResult();
      } catch (Exception) {
        throw new EmailNotFound();
      }

      if ($account->getEmailVerified())
        throw new EmailAlreadyVerified();

      $payload = [
        'email' => $beginEmailVerification->email
      ];
      $token = $this->jwt->create($payload, '1 hour');

      return $this->emailService->sendEmailVerification($beginEmailVerification->email, $token);
    }



    /**
     * @throws EmailAlreadyVerified
     * @throws InvalidToken
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TokenExpired
     * @throws EmailNotFound
     */
    public function completeEmailVerification(CompleteEmailVerificationInput $completeEmailVerification): bool {
      $payload = $this->jwt->decode($completeEmailVerification->token);

      try {
        /** @var $account AccountEntity */
        $account = $this->repository->createQueryBuilder('a')
          ->select('a')
          ->where('a.email = :email')
          ->setParameter('email', $payload['email'])
          ->getQuery()
          ->getSingleResult();
      } catch (Exception) {
        throw new EmailNotFound();
      }

      if ($account->getEmailVerified())
        throw new EmailAlreadyVerified();

      $account->setEmailVerified(true);
      $this->entityManager->flush($account);

      return true;
    }



    /**
     * @throws EmailNotFound
     * @throws Exception
     */
    public function beginPasswordReset(BeginPasswordResetInput $resetPassword): bool {
      try {
        // check if account exists
        $emailsCount = (int) $this->repository->createQueryBuilder('a')
          ->select('COUNT(a.id)')
          ->where('a.email = :email')
          ->setParameter('email', $resetPassword->email)
          ->getQuery()
          ->getSingleScalarResult();
      } catch (Exception) {
        throw new EmailNotFound();
      }

      if ($emailsCount === 0)
        throw new EmailNotFound();

      $payload = [
        'email' => $resetPassword->email,
        'newPassword' => $resetPassword->newPassword
      ];
      $token = $this->jwt->create($payload, '1 hour');

      return $this->emailService->sendPasswordResetVerification($resetPassword->email, $token);
    }



    /**
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws EmailNotFound
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function completePasswordReset(CompletePasswordResetInput $completePasswordReset): bool {
      $payload = $this->jwt->decode($completePasswordReset->token);
      $email = $payload['email'];
      $newPassword = $payload['newPassword'];

      try {
        /* @var AccountEntity $account */
        $account = $this->repository->createQueryBuilder('a')
          ->select('a')
          ->where('a.email = :email')
          ->setParameter('email', $email)
          ->getQuery()
          ->getSingleResult();
      } catch (Exception) {
        throw new EmailNotFound();
      }

      $account->setPassword($newPassword);
      $this->entityManager->flush($this->currentAccount);

      return true;
    }



    public function uploadAvatar(UploadAvatarInput $uploadAvatar): AccountEntity {
      // TODO: Implement

      return $this->currentAccount;
    }



    /**
     * @throws EmailNotFound
     */
    public function beginAccountRemoval(): bool {
      $payload = [
        'email' => $this->currentAccount->getEmail()
      ];
      $token = $this->jwt->create($payload, '1 hour');

      return $this->emailService->sendAccountRemovalVerification($this->currentAccount->getEmail(), $token);
    }



    /**
     * @throws EmailNotFound
     * @throws OptimisticLockException
     * @throws InvalidToken
     * @throws ORMException
     * @throws TokenExpired
     */
    public function completeAccountRemoval(CompleteAccountRemovalInput $completeAccountRemoval): bool {
      $this->jwt->decode($completeAccountRemoval->token);
      $this->currentAccount->setIsMarkedAsRemoved(true);
      $this->entityManager->flush($this->currentAccount);

      return true;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws MustBeMarkedAsRemovedFirst
     */
    public function permanentlyDeleteAccount(): AccountEntity {
      if (!$this->currentAccount->getIsMarkedAsRemoved())
        throw new MustBeMarkedAsRemovedFirst();

      $this->entityManager->remove($this->currentAccount);
      $this->entityManager->flush($this->currentAccount);

      return $this->currentAccount;
    }
  }
}
