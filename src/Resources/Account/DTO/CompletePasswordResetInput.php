<?php



namespace App\Resources\Account\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class CompletePasswordResetInput {
    #[Field]
    public string $token;
  }
}
