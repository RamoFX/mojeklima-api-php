<?php



namespace App\GraphQL\Exceptions {

  use App\GraphQL\Services\HeadersService;
  use App\Utilities\Translation;
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
