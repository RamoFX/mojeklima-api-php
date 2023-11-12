<?php



namespace App\Resources\PushSubscription\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class SubscribeForPushNotificationsInput {
    #[Field]
    public string $userAgent;

    #[Field]
    public string $endpoint;

    #[Field]
    public string $p256dh;

    #[Field]
    public string $auth;
  }
}
