<?php



namespace App\Resources\Auth\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class IncorrectPassword extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        "cs" => "Heslo není správné",
        "en" => "Incorrect password",
        "de" => "Falsches Passwort",
      ]);

      parent::__construct($message, 401);
    }
  }
}
