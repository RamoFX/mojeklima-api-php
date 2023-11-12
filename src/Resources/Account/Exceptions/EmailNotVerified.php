<?php



namespace App\Resources\Account\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class EmailNotVerified extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        'cs' => 'E-mailová adresa ještě není ověřena',
        'en' => 'Email address is not yet verified',
        'de' => 'E-Mail-Adresse ist noch nicht verifiziert',
      ]);

      parent::__construct($message, 409);
    }
  }
}
