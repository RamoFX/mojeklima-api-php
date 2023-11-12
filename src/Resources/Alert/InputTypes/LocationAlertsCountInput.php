<?php



namespace App\Resources\Alert\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class LocationAlertsCountInput {
    #[Field]
    public int $locationId;
  }
}
