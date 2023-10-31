<?php



namespace App\Resources\Auth\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class BearerTokenMissing extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        "cs" => "Bearer token je povinný, ale chybí",
        "en" => "Bearer token is required but missing",
        "de" => "Bearer-Token ist erforderlich, fehlt aber",
      ]);

      parent::__construct($message, 401);
    }
  }
}
