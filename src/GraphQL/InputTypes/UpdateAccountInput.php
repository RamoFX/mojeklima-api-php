<?php



namespace App\GraphQL\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  /** @Input() */
  class UpdateAccountInput {
    /** @Field() */
    public ?string $name;

    /** @Field() */
    public ?string $email;

    /** @Field() */
    public ?string $password;
  }
}
