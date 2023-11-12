<?php



namespace App\Resources\Notification\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class NotificationInput {
    #[Field]
    public int $id;
  }
}
