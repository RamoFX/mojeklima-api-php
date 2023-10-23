<?php



namespace App\Resources\PushSubscription {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Common\Utilities\GlobalProxy;
  use Doctrine\ORM\Exception\ORMException;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;



  readonly class PushSubscriptionController {
    private PushSubscriptionService $pushSubscriptionService;



    /**
     * @throws Exception
     */
    public function __construct() {
      $this->pushSubscriptionService = GlobalProxy::$container->get(PushSubscriptionService::class);
    }



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
