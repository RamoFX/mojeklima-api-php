<?php



namespace App\Resources\Location\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class LocationInput {
    #[Field]
    public int $id;
  }
}
