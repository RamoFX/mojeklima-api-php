<?php



namespace App\Resources\PushSubscription {

  use App\Resources\Account\AccountEntity;
  use Doctrine\ORM\Exception\ORMException;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
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
    public function subscribeForPushNotifications(#[InjectUser] AccountEntity $currentAccount, string $userAgent, string $endpoint, string $p256dh, string $auth): PushSubscriptionEntity {
      return $this->pushSubscriptionService->subscribeForPushNotifications($currentAccount, $userAgent, $endpoint, $p256dh, $auth);
    }
  }
}
