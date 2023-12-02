<?php



namespace App\Resources\Notification\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;
  use TheCodingMachine\GraphQLite\Types\ID;



  #[Input]
  class NotificationInput {
    #[Field]
    public ID $id;
  }
}
