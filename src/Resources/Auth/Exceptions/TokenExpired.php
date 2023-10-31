<?php



namespace App\Resources\Auth\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class TokenExpired extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        "cs" => "Token již vypršel",
        "en" => "The token has expired",
        "de" => "Das Token ist abgelaufen",
      ]);

      parent::__construct($message, 401);
    }
  }
}
