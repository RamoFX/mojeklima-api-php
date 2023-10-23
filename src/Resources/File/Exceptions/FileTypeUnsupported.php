<?php



namespace App\Resources\File\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class FileTypeUnsupported extends GraphQLException {
    public function __construct(string $type, array $allowedTypes) {
      $language = Translation::getPreferredLanguage();
      $messages = [
        "cs" => "Typ souboru \"$type\" není podporován. Podporované typy souboru: ",
        "en" => "File type \"$type\" is unsupported. Supported file types: ",
        "de" => "Der \"$type\" Dateityp wird nicht unterstützt. Unterstützte Dateitypen: ",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage . implode(', ', $allowedTypes), 400);
    }
  }
}
