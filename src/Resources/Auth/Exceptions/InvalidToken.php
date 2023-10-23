<?php



namespace App\Resources\Auth\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class InvalidToken extends GraphQLException {
    public function __construct() {
      $language = Translation::getPreferredLanguage();
      $messages = [
        "cs" => "Token je neplatný",
        "en" => "Invalid token",
        "de" => "Ungültiges Token",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 401);
    }
  }
}
