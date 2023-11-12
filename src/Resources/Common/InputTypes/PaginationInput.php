<?php



namespace App\Resources\Common\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class PaginationInput {
    #[Field]
    public int $currentPage;

    #[Field]
    public int $perPage;
  }
}
