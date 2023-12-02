<?php



namespace App\Resources\Account\DTO {

  use App\Resources\Account\Enums\AccountRole;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;
  use TheCodingMachine\GraphQLite\Types\ID;



  #[Input]
  class ChangeRoleInput {
    #[Field]
    public ID $id;

    #[Field]
    public AccountRole $role;
  }
}
