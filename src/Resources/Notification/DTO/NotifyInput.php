<?php



namespace App\Resources\Notification\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class NotifyInput {
    #[Field(outputType: "ID")]
    public int $accountId;

    #[Field(outputType: "ID")]
    public int $alertId;
  }
}
