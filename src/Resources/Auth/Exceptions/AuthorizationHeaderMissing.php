<?php



namespace App\Resources\Auth\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class AuthorizationHeaderMissing extends GraphQLException {
    public function __construct() {
      $language = Translation::getPreferredLanguage();
      $messages = [
        "cs" => "Autorizační hlavička je povinná, ale chybí",
        "en" => "Authorization header required but missing",
        "de" => "Autorisierungsheader erforderlich, aber fehlt",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 401);
    }
  }
}
