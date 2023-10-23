<?php



namespace App\Resources\Auth\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class IncorrectPassword extends GraphQLException {
    public function __construct() {
      $language = Translation::getPreferredLanguage();
      $messages = [
        "cs" => "Heslo není správné",
        "en" => "Incorrect password",
        "de" => "Falsches Passwort",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 401);
    }
  }
}
