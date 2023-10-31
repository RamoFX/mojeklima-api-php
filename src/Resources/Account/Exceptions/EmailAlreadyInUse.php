<?php



namespace App\Resources\Account\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class EmailAlreadyInUse extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        "cs" => "Emailová adresa se již používá",
        "en" => "Email address is already in use",
        "de" => "E-Mail-Adresse bereits vergeben",
      ]);

      parent::__construct($message, 409);
    }
  }
}
