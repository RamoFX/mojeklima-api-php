<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Weather;
  use App\Core\Enums\PressureUnits;
  use App\Core\Enums\SpeedUnits;
  use App\Core\Enums\TemperatureUnits;
  use App\External\OpenWeatherApi;
  use App\GraphQL\Exceptions\EntityNotFound;
  use App\Utilities\Translation;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class OpenWeatherApiController {
    /**
     * @throws EntityNotFound
     * @throws GraphQLException
     * @throws Exception
     */
    #[Query]
    #[Logged]
    public static function weather(#[InjectUser] Account $currentAccount, int $locationId, ?TemperatureUnits $temperatureUnits, ?SpeedUnits $speedUnits, ?PressureUnits $pressureUnits): Weather {
      $location = LocationController::location($currentAccount, $locationId);
      $language = Translation::get_preferred_language();

      // Open Weather API inconsistency: API supports both "en" (language code) and "cz" (country code).
      // For that reason mapping needs to be done
      $languageMap = [
        'cs' => 'cz'
      ];

      $weather = OpenWeatherApi::get_weather(
        $location->getLatitude(),
        $location->getLongitude(),
        $languageMap[$language] ?? $language
      );

      $weather->convertTemperature($temperatureUnits ?? TemperatureUnits::CELSIUS);
      $weather->convertSpeed($speedUnits ?? SpeedUnits::METERS_PER_SECOND);
      $weather->convertPressure($pressureUnits ?? PressureUnits::HECTOPASCAL);

      return $weather;
    }
  }
}
