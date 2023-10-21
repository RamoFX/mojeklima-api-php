<?php



namespace App\GraphQL\Exceptions {

  use App\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class AccountMarkedAsRemoved extends GraphQLException {
    public function __construct() {
      $language = Translation::get_preferred_language();
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
