<?php



namespace App\Resources\PushSubscription {

  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Common\CommonService;
  use App\Resources\PushSubscription\DTO\SubscribeForPushNotificationsInput;
  use DI\DependencyException;
  use DI\NotFoundException;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use Doctrine\ORM\TransactionRequiredException;



  class PushSubscriptionService extends CommonService {
    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws DependencyException
     * @throws NotFoundException
     * @throws TransactionRequiredException
     */
    public function __construct(
      protected EntityManager $entityManager
    ) {
      parent::__construct();
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function subscribeForPushNotifications(SubscribeForPushNotificationsInput $subscribeForPushNotifications): PushSubscriptionEntity {
      // if push subscription exists then update it
      $pushSubscriptions = $this->currentAccount->getPushSubscriptions();

      foreach ($pushSubscriptions as $pushSubscription) {
        if ($pushSubscription->getUserAgent() === $subscribeForPushNotifications->userAgent) {
          $pushSubscription->setEndpoint($subscribeForPushNotifications->endpoint);
          $pushSubscription->setP256dh($subscribeForPushNotifications->p256dh);
          $pushSubscription->setAuth($subscribeForPushNotifications->auth);

          $this->entityManager->flush($pushSubscription);

          return $pushSubscription;
        }
      }

      // create new
      $pushSubscription = new PushSubscriptionEntity(
        $subscribeForPushNotifications->endpoint,
        $subscribeForPushNotifications->p256dh,
        $subscribeForPushNotifications->auth,
        $subscribeForPushNotifications->userAgent
      );

      $this->currentAccount->addPushSubscription($pushSubscription);
      $this->entityManager->persist($pushSubscription);
      $this->entityManager->flush($pushSubscription);

      return $pushSubscription;
    }
  }
}
