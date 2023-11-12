<?php



namespace App\Resources\Weather\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class WeatherInput {
    #[Field]
    public int $locationId;
  }
}
