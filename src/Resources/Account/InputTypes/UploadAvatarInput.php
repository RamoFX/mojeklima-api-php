<?php



namespace App\Resources\Account\InputTypes {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class UploadAvatarInput {
    #[Field]
    public string $url;
  }
}
