<?php



namespace App\GraphQL\Exceptions {

  use App\GraphQL\Services\HeadersService;
  use App\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class EmailNotFound extends GraphQLException {
    public function __construct() {
      $language = Translation::getPreferredLanguage();
      $messages = [
        "cs" => "Emailová adresa nebyla nalezena",
        "en" => "Email address wasn't found",
        "de" => "E-Mail-Adresse nicht gefunden",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 404);
    }
  }
}
