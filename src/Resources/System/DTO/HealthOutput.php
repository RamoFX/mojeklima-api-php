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
      public bool $redisHealthy,
      #[Field]
      public bool $databaseHealthy
    ) {}
  }
}
