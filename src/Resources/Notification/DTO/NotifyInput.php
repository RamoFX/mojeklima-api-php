<?php



namespace App\Resources\Notification\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;
  use TheCodingMachine\GraphQLite\Types\ID;



  #[Input]
  class NotifyInput {
    #[Field]
    public ID $accountId;

    #[Field]
    public ID $alertId;
  }
}
