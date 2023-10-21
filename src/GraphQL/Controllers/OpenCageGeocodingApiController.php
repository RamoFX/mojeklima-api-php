<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Suggestion;
  use App\External\OpenCageGeocodingApi;
  use App\Utilities\Translation;
  use RestClientException;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  class OpenCageGeocodingApiController {
    /**
     * @return Suggestion[]
     * @throws RestClientException
     */
    #[Query]
    #[Logged]
    public static function suggestions(string $query): array {
      $language = Translation::get_preferred_language();

      return OpenCageGeocodingApi::get_suggestions($language, $query);
    }
  }
}
