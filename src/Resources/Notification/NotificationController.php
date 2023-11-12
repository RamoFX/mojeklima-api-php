<?php



namespace App\Resources\Notification {

  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Notification\DTO\DeleteNotificationInput;
  use App\Resources\Notification\DTO\NotificationInput;
  use App\Resources\Notification\DTO\NotifyInput;
  use Doctrine\ORM\Exception\NotSupported;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use ErrorException;
  use Exception;
  use Psr\SimpleCache\InvalidArgumentException;
  use RestClientException;
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
     * @throws Exception
     */
    #[Query]
    #[Logged]
    public function notifications(): array {
      return $this->notificationService->notifications();
    }



    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public function notification(NotificationInput $notification): NotificationEntity {
      return $this->notificationService->notification($notification);
    }



    /**
     * @throws Exception
     */
    #[Query]
    #[Logged]
    public function hasUnseen(): bool {
      return $this->notificationService->hasUnseen();
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     * @throws Exception
     */
    #[Mutation]
    #[Logged]
    public function seenAll(): int {
      return $this->notificationService->seenAll();
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
    public function notify(NotifyInput $notify): NotificationEntity {
      return $this->notificationService->notify($notify);
    }

    /**
     * @TODO Rework error handling - catch errors
     *   and provide custom ones - it is not clear
     *   why for example in this method we should
     *   handle InvalidArgumentException
     *   - there are no arguments!
     * @throws OptimisticLockException
     * @throws GraphQLException
     * @throws ORMException
     * @throws EntityNotFound
     * @throws NotSupported
     * @throws RestClientException
     * @throws ErrorException
     * @throws InvalidArgumentException
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
    public function deleteNotification(DeleteNotificationInput $deleteNotification): NotificationEntity {
      return $this->notificationService->deleteNotification($deleteNotification);
    }
  }
}
