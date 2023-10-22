<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\PushSubscription;
  use App\GlobalProxy;
  use Doctrine\ORM\Exception\ORMException;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;



  class PushSubscriptionController {
    /**
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public static function subscribeForPushNotifications(#[InjectUser] Account $currentAccount, string $userAgent, string $endpoint, string $p256dh, string $auth): PushSubscription {
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
      $push_subscription = new PushSubscription($endpoint, $p256dh, $auth, $userAgent);

      $currentAccount->addPushSubscription($push_subscription);

      GlobalProxy::$entityManager->persist($push_subscription);
      GlobalProxy::$entityManager->flush($push_subscription);

      return $push_subscription;
    }
  }
}
