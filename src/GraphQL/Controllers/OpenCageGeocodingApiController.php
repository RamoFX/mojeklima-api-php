<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Suggestion;
  use App\Core\Entities\Weather;
  use App\External\OpenCageGeocodingApi;
  use App\External\OpenWeatherApi;
  use App\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  class OpenCageGeocodingApiController {
    /**
     * @Query()
     * @Logged()
     * @return Suggestion[]
     */
    public static function suggestions(string $query): array {
      $language = Translation::getPreferredLanguage();
      return OpenCageGeocodingApi::getSuggestions($language, $query);
    }
  }
}
