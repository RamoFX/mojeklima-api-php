<?php



namespace App\Resources\Notification\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class NotifyInput {
    #[Field]
    public int $accountId;

    #[Field]
    public int $alertId;
  }
}
