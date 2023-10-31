<?php



namespace App\Resources\Common\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class EntityNotFound extends GraphQLException {
    public function __construct(string $entityName) {
      $message = Translation::translate([
        "cs" => "Entita \"$entityName\" nebyla nalezena",
        "en" => "Entity \"$entityName\" wasn't found",
        "de" => "Entität \"$entityName\" nicht gefunden",
      ]);

      parent::__construct($message, 404);
    }
  }
}
