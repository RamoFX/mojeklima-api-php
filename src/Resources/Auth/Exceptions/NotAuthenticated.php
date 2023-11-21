<?php



namespace App\Resources\Auth\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class NotAuthenticated extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        "cs" => "Nejste přihlášení",
        "en" => "You are not logged in",
        "de" => "Sie sind nicht eingeloggt",
      ]);

      parent::__construct($message, 401);
    }
  }
}
