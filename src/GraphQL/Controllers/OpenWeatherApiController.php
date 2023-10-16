<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Weather;
  use App\Core\Enums\PressureUnitsEnum;
  use App\Core\Enums\SpeedUnitsEnum;
  use App\Core\Enums\TemperatureUnitsEnum;
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
    public static function weather(Account $currentAccount, int $locationId, ?TemperatureUnitsEnum $temperatureUnits, ?SpeedUnitsEnum $speedUnits, ?PressureUnitsEnum $pressureUnits): Weather {
      $location = LocationController::location($currentAccount, $locationId);
      $language = Translation::get_preferred_language();
      // Open Weather API inconsistency: API supports both "en" (language code) and "cz" (coutnry code).
      // For that reason mapping needs to be done
      $languageMap = [
        'cs' => 'cz'
      ];
      
      $weather = OpenWeatherApi::get_weather(
        $location->getLatitude(),
        $location->getLongitude(),
        $languageMap[$language] ?? $language
      );

      $weather->convertTemperature($temperatureUnits ?? TemperatureUnitsEnum::CELSIUS());
      $weather->convertSpeed($speedUnits ?? SpeedUnitsEnum::METERS_PER_SECOND());
      $weather->convertPressure($pressureUnits ?? PressureUnitsEnum::HECTOPASCAL());

      return $weather;
    }
  }
}
