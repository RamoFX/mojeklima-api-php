<?php



namespace App\Resources\Alert\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;
  use TheCodingMachine\GraphQLite\Types\ID;



  #[Input]
  class LocationAlertsInput {
    #[Field]
    public ID $locationId;
  }
}
