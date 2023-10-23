<?php



namespace App\Resources\PushSubscription {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Common\Utilities\GlobalProxy;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;



  class PushSubscriptionService {
    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function subscribeForPushNotifications(AccountEntity $currentAccount, string $userAgent, string $endpoint, string $p256dh, string $auth): PushSubscriptionEntity {
      // if push subscription exists then update it
      $push_subscriptions = $currentAccount->getPushSubscriptions();

      foreach ($push_subscriptions as $push_subscription) {
        if ($push_subscription->getUserAgent() === $userAgent) {
          $push_subscription->setEndpoint($endpoint);
          $push_subscription->setP256dh($p256dh);
          $push_subscription->setAuth($auth);

          GlobalProxy::$entityManager->persist($push_subscription);
          GlobalProxy::$entityManager->flush($push_subscription);

          return $push_subscription;
        }
      }

      // create new
      $push_subscription = new PushSubscriptionEntity($endpoint, $p256dh, $auth, $userAgent);

      $currentAccount->addPushSubscription($push_subscription);

      GlobalProxy::$entityManager->persist($push_subscription);
      GlobalProxy::$entityManager->flush($push_subscription);

      return $push_subscription;
    }
  }
}
