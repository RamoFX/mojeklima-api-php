<?php



namespace App\Resources\Alert\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class ToggleAlertInput {
    #[Field]
    public int $id;

    #[Field]
    public bool $isEnabled;
  }
}
