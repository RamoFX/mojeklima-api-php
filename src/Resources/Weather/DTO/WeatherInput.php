<?php



namespace App\Resources\Weather\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;
  use TheCodingMachine\GraphQLite\Types\ID;



  #[Input]
  class WeatherInput {
    public function __construct(
      #[Field]
      public ID $locationId
    ) {}
  }
}
