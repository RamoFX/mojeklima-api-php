<?php



namespace App\Resources\Auth\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class BearerTokenMissing extends GraphQLException {
    public function __construct() {
      $language = Translation::getPreferredLanguage();
      $messages = [
        "cs" => "Bearer token je povinný, ale chybí",
        "en" => "Bearer token is required but missing",
        "de" => "Bearer-Token ist erforderlich, fehlt aber",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 401);
    }
  }
}
