<?php



namespace App\Resources\PushSubscription {

  use App\Resources\PushSubscription\InputTypes\SubscribeForPushNotificationsInput;
  use Doctrine\ORM\Exception\ORMException;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;



  readonly class PushSubscriptionController {
    public function __construct(
      protected PushSubscriptionService $pushSubscriptionService
    ) {}



    /**
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public function subscribeForPushNotifications(SubscribeForPushNotificationsInput $subscribeForPushNotifications): PushSubscriptionEntity {
      return $this->pushSubscriptionService->subscribeForPushNotifications($subscribeForPushNotifications);
    }
  }
}
