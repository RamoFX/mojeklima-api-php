<?php



namespace App\Resources\System\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  #[Type]
  #[Input]
  class HealthOutput {
    public function __construct(
      #[Field]
      public bool $redis,
      #[Field]
      public bool $database,
      #[Field]
      public bool $overall
    ) {}
  }
}
