<?php



namespace App\Resources\Account\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class AccountMarkedAsRemoved extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        "cs" => "Tento účet byl označen jako odstraněný",
        "en" => "This account was marked as removed",
        "de" => "This account was marked as removed",
      ]);

      parent::__construct($message, 409);
    }
  }
}
