<?php



namespace App\Resources\Alert\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;
  use TheCodingMachine\GraphQLite\Types\ID;



  #[Input]
  class ToggleAlertInput {
    #[Field]
    public ID $id;

    #[Field]
    public bool $isEnabled;
  }
}
