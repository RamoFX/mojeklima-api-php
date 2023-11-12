<?php



namespace App\Resources\Account\DTO {

  use App\Resources\Account\Enums\AccountRole;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class ChangeRoleInput {
    #[Field]
    public int $id;

    #[Field]
    public AccountRole $role;
  }
}
