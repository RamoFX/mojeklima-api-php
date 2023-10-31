<?php



namespace App\Resources\Account\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class EmailNotFound extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        "cs" => "Emailová adresa nebyla nalezena",
        "en" => "Email address wasn't found",
        "de" => "E-Mail-Adresse nicht gefunden",
      ]);

      parent::__construct($message, 404);
    }
  }
}
