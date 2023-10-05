<?php



namespace App\GraphQL\Exceptions {

  use App\GraphQL\Services\HeadersService;
  use App\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class TokenExpired extends GraphQLException {
    public function __construct() {
      $language = Translation::get_preferred_language();
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
