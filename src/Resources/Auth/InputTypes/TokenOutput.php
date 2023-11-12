<?php



namespace App\Resources\Auth\InputTypes {

  use DateTimeImmutable;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  #[Input]
  #[Type(name: 'Token')]
  class TokenOutput {
    #[Field]
    public string $token;

    #[Field]
    public DateTimeImmutable $nextRenewalAt;
  }
}
