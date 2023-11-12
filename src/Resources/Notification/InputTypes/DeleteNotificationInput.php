<?php



namespace App\Resources\Notification\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class DeleteNotificationInput {
    #[Field]
    public int $id;
  }
}
