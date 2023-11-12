<?php



namespace App\Resources\Location\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class DeleteLocationInput {
    #[Field]
    public int $id;
  }
}
