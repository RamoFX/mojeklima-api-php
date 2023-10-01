<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Notification;
  use App\Core\Entities\PushSubscription;
  use App\Core\EntityManagerProxy;
  use App\GraphQL\DevelopmentOutputBuffer;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Annotations\Right;



  class PushSubscriptionController {
    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function subscribeForPushNotifications(Account $currentAccount, string $userAgent, string $endpoint, string $p256dh, string $auth): PushSubscription {
      // if push subscription exists then update it
      $pushSubscriptions = $currentAccount->getPushSubscriptions();

      foreach ($pushSubscriptions as $pushSubscription) {
        if ($pushSubscription->getUserAgent() === $userAgent) {
          $pushSubscription->setEndpoint($endpoint);
          $pushSubscription->setP256dh($p256dh);
          $pushSubscription->setAuth($auth);

          EntityManagerProxy::$entity_manager->persist($pushSubscription);
          EntityManagerProxy::$entity_manager->flush($pushSubscription);

          return $pushSubscription;
        }
      }

      // create new
      $pushSubscription = new PushSubscription($endpoint, $p256dh, $auth, $userAgent);

      $currentAccount->addPushSubscription($pushSubscription);

      EntityManagerProxy::$entity_manager->persist($pushSubscription);
      EntityManagerProxy::$entity_manager->flush($pushSubscription);

      return $pushSubscription;
    }
  }
}
