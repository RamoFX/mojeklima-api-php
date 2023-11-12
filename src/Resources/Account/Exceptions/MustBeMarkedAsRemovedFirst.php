<?php



namespace App\Resources\Account\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class MustBeMarkedAsRemovedFirst extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        'cs' => 'Účet musí být nejprve označen jako odstraněný',
        'en' => 'Account must be marked as removed first',
        'de' => 'Das Konto muss zuerst als entfernt markiert werden',
      ]);

      parent::__construct($message, 400);
    }
  }
}
