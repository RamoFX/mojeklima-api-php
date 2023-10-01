<?php



namespace App\GraphQL\Exceptions {

  use App\GraphQL\Services\HeadersService;
  use App\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class EntityNotFound extends GraphQLException {
    public function __construct(string $entityName) {
      $language = Translation::getPreferredLanguage();
      $messages = [
        "cs" => "Entita \"$entityName\" nebyla nalezena",
        "en" => "Entity \"$entityName\" wasn't found",
        "de" => "Entität \"$entityName\" nicht gefunden",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 404);
    }
  }
}
