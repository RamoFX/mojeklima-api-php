<?php



namespace App\Resources\Location\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class LocationInput {
    public function __construct(
      #[Field(outputType: "ID")]
      public int $id
    ) {}
  }
}
