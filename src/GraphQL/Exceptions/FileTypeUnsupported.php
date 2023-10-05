<?php



namespace App\GraphQL\Exceptions {

  use App\GraphQL\Services\HeadersService;
  use App\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class FileTypeUnsupported extends GraphQLException {
    public function __construct(string $type, array $allowedTypes) {
      $language = Translation::get_preferred_language();
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
