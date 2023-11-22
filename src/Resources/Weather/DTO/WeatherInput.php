<?php



namespace App\Resources\Weather\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class WeatherInput {
    public function __construct(
      #[Field]
      public int $locationId
    ) {}
  }
}
