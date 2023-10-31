<?php



namespace App\Resources\File\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class FileTypeUnsupported extends GraphQLException {
    public function __construct(string $type, array $allowedTypes) {
      $message = Translation::translate([
        "cs" => "Typ souboru \"$type\" není podporován. Podporované typy souboru: ",
        "en" => "File type \"$type\" is unsupported. Supported file types: ",
        "de" => "Der \"$type\" Dateityp wird nicht unterstützt. Unterstützte Dateitypen: ",
      ]);

      parent::__construct($message . implode(', ', $allowedTypes), 400);
    }
  }
}
