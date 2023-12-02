<?php



namespace App\Resources\Location\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;
  use TheCodingMachine\GraphQLite\Types\ID;



  #[Input]
  class DeleteLocationInput {
    #[Field]
    public ID $id;
  }
}
