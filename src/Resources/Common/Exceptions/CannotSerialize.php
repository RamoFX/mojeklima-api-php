<?php



namespace App\Resources\Common\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class CannotSerialize extends GraphQLException {
    public function __construct(string $entityName) {
      $message = Translation::translate([
        "cs" => "Nelze serializovat entitu \"$entityName\"",
        "en" => "Cannot serialize entity \"$entityName\"",
        "de" => "Entität \"entityName\" kann nicht serialisiert werden",
      ]);

      parent::__construct($message, 500);
    }
  }
}
