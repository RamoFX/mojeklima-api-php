<?php



namespace App\Resources\Alert\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class DeleteAlertInput {
    #[Field]
    public int $id;
  }
}
