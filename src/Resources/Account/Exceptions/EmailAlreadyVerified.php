<?php



namespace App\Resources\Account\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class EmailAlreadyVerified extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        'cs' => 'E-mailová adresa je již ověřena',
        'en' => 'Email address is already verified',
        'de' => 'E-Mail-Adresse ist bereits verifiziert',
      ]);

      parent::__construct($message, 409);
    }
  }
}
