<?php



namespace App\Resources\Auth\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class LoginInput {
    #[Field]
    public string $email;

    #[Field]
    public string $password;
  }
}
