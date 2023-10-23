<?php



namespace App\Resources\File\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class FileMissing extends GraphQLException {
    public function __construct() {
      $language = Translation::getPreferredLanguage();
      $messages = [
        "cs" => "Soubor je povinný, ale chybí",
        "en" => "File is required but missing",
        "de" => "Datei erforderlich, aber fehlt",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 400);
    }
  }
}
