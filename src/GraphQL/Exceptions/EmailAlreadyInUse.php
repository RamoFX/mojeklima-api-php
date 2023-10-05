<?php



namespace App\GraphQL\Exceptions {

  use App\GraphQL\Services\HeadersService;
  use App\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class EmailAlreadyInUse extends GraphQLException {
    public function __construct() {
      $language = Translation::get_preferred_language();
      $messages = [
        "cs" => "Emailová adresa je již použita",
        "en" => "Email address is already in use",
        "de" => "E-Mail-Adresse bereits vergeben",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 409);
    }
  }
}
