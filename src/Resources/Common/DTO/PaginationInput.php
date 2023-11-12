<?php



namespace App\Resources\Common\DTO {

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
