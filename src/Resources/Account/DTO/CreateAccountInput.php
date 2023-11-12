<?php



namespace App\Resources\Account\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class CreateAccountInput {
    #[Field]
    public string $name;

    #[Field]
    public string $email;

    #[Field]
    public string $password;
  }
}
