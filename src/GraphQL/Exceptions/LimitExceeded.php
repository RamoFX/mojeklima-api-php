<?php



namespace App\GraphQL\Exceptions {

  use App\GraphQL\Services\HeadersService;
  use App\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class LimitExceeded extends GraphQLException {
    /**
     * @param string $entityName
     * @param float|int $limit
     */
    public function __construct(string $entityName, $limit) {
      $language = Translation::get_preferred_language();
      $messages = [
        "cs" => "Limit vyčerpán, entita: \"$entityName\", limit: $limit",
        "en" => "Limit exceeded, entity: \"$entityName\", limit: $limit",
        "de" => "Grenzwert überschritten, Entität: \"$entityName\", Grenzwert: $limit",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      parent::__construct($translatedMessage, 404);
    }
  }
}
