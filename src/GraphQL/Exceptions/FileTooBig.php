<?php



namespace App\GraphQL\Exceptions {

  use App\GraphQL\Services\HeadersService;
  use App\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class FileTooBig extends GraphQLException {
    public function __construct(int $limit) {
      $language = Translation::get_preferred_language();
      $messages = [
        "cs" => "Soubor je příliž velký, maximální velikost je $limit bajtů",
        "en" => "The file is too large, the maximum size is $limit bytes",
        "de" => "Die Datei ist zu groß, die maximale Größe beträgt $limit Bytes",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 400);
    }
  }
}
