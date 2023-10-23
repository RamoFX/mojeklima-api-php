<?php



namespace App\Resources\Account\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class CreateAccount {
    #[Field]
    public string $name;

    #[Field]
    public string $email;
  }
}
