<?php



namespace App\Resources\Notification {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Common\Utilities\GlobalProxy;
  use Doctrine\ORM\Exception\NotSupported;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use ErrorException;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Annotations\Right;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  readonly class NotificationController {
    public function __construct(
      protected NotificationService $notificationService
    ) {}



    /**
     * @return NotificationEntity[]
     */
    #[Query]
    #[Logged]
    public function notifications(#[InjectUser] AccountEntity $currentAccount): array {
      return $this->notificationService->notifications($currentAccount);
    }



    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public function notification(#[InjectUser] AccountEntity $currentAccount, int $id): NotificationEntity {
      return $this->notificationService->notification($currentAccount, $id);
    }



    #[Query]
    #[Logged]
    public function hasUnseen(#[InjectUser] AccountEntity $currentAccount): bool {
      return $this->notificationService->hasUnseen($currentAccount);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public function seenAll(#[InjectUser] AccountEntity $currentAccount): int {
      return $this->notificationService->seenAll($currentAccount);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws EntityNotFound
     * @throws ErrorException
     */
    #[Mutation]
    #[Logged]
    #[Right("PUSH_NOTIFICATIONS")]
    public function notify(int $accountId, int $alertId): NotificationEntity {
      return $this->notificationService->notify($accountId, $alertId);
    }

    /**
     * @throws OptimisticLockException
     * @throws GraphQLException
     * @throws ORMException
     * @throws EntityNotFound
     * @throws NotSupported
     * @throws ErrorException
     */
    #[Mutation]
    #[Logged]
    #[Right("PUSH_NOTIFICATIONS")]
    public function checkForNotifications(): int {
      return $this->notificationService->checkForNotifications();
    }



    /**
     * @throws EntityNotFound
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public function deleteNotification(#[InjectUser] AccountEntity $currentAccount, int $id): NotificationEntity {
      $notification = self::notification($currentAccount, $id);

      GlobalProxy::$entityManager->remove($notification);
      GlobalProxy::$entityManager->flush($notification);

      return $notification;
    }
  }
}
