<?php



namespace App\Resources\Alert\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class LocationAlertsCountInput {
    #[Field(outputType: "ID")]
    public int $locationId;
  }
}
