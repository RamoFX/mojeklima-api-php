<?php



namespace App\Resources\Account\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class EmailAlreadyInUse extends GraphQLException {
    public function __construct() {
      $language = Translation::getPreferredLanguage();
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
