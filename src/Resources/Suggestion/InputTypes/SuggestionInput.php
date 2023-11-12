<?php



namespace App\Resources\Suggestion\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class SuggestionInput {
    #[Field]
    public string $query;
  }
}
