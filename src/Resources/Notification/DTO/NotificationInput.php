<?php



namespace App\Resources\Notification\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class NotificationInput {
    #[Field(outputType: "ID")]
    public int $id;
  }
}
