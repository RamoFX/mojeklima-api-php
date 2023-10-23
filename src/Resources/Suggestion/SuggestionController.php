<?php



namespace App\Resources\Suggestion {

  use App\Resources\Common\Utilities\Translation;
  use RestClientException;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  class SuggestionController {
    /**
     * @return SuggestionEntity[]
     * @throws RestClientException
     */
    #[Query]
    #[Logged]
    public static function suggestions(string $query): array {
      $language = Translation::getPreferredLanguage();

      return SuggestionService::get_suggestions($language, $query);
    }
  }
}
