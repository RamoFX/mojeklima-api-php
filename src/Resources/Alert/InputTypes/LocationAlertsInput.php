<?php



namespace App\Resources\Alert\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class LocationAlertsInput {
    #[Field]
    public int $locationId;
  }
}
