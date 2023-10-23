<?php



namespace App\Resources\Account\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class AccountAlreadyExist extends GraphQLException {
    public function __construct() {
      $language = Translation::getPreferredLanguage();
      $messages = [
        "cs" => "Účet s touto emailovou adresou již existuje",
        "en" => "An account with this email address already exists",
        "de" => "Ein Konto mit dieser E-Mail-Adresse existiert bereits",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 409);
    }
  }
}
