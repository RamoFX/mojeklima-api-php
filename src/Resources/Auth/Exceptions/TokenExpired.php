<?php



namespace App\Resources\Auth\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class TokenExpired extends GraphQLException {
    public function __construct() {
      $language = Translation::getPreferredLanguage();
      $messages = [
        "cs" => "Token již vypršel",
        "en" => "The token has expired",
        "de" => "Das Token ist abgelaufen",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 401);
    }
  }
}
