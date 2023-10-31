<?php



namespace App\Resources\Auth\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class InvalidToken extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        "cs" => "Token je neplatný",
        "en" => "Invalid token",
        "de" => "Ungültiges Token",
      ]);

      parent::__construct($message, 401);
    }
  }
}
