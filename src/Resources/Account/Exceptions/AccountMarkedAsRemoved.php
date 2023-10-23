<?php



namespace App\Resources\Account\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class AccountMarkedAsRemoved extends GraphQLException {
    public function __construct() {
      $language = Translation::getPreferredLanguage();
      $messages = [
        "cs" => "Tento účet byl označen jako odstraněný",
        "en" => "This account was marked as removed",
        "de" => "This account was marked as removed",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 409);
    }
  }
}
