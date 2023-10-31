<?php



namespace App\Resources\File\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class FileMissing extends GraphQLException {
    public function __construct() {
      $message = Translation::translate([
        "cs" => "Soubor je povinný, ale chybí",
        "en" => "File is required but missing",
        "de" => "Datei erforderlich, aber fehlt",
      ]);

      parent::__construct($message, 400);
    }
  }
}
