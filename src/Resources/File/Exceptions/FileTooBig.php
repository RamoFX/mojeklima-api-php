<?php



namespace App\Resources\File\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class FileTooBig extends GraphQLException {
    public function __construct(int $limit) {
      $message = Translation::translate([
        "cs" => "Soubor je příliž velký, maximální velikost je $limit bajtů",
        "en" => "The file is too large, the maximum size is $limit bytes",
        "de" => "Die Datei ist zu groß, die maximale Größe beträgt $limit Bytes",
      ]);

      parent::__construct($message, 400);
    }
  }
}
