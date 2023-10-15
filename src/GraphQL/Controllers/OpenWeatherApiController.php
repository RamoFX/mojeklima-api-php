<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Weather;
  use App\Core\Enums\WeatherUnitsEnum;
  use App\External\OpenWeatherApi;
  use App\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  class OpenWeatherApiController {
    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function weather(Account $currentAccount, int $locationId, WeatherUnitsEnum $units): Weather {
      $location = LocationController::location($currentAccount, $locationId);
      $language = Translation::get_preferred_language();
      // Open Weather API inconsistency: API supports both "en" (language code) and "cz" (coutnry code).
      // For that reason mapping needs to be done
      $languageMap = [
        'cs' => 'cz'
      ];
      
      return OpenWeatherApi::get_weather(
        $location->getLatitude(),
        $location->getLongitude(),
        $units,
        $languageMap[$language] ?? $language
      );
    }
  }
}
