<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Weather;
  use App\Core\Enums\PressureUnits;
  use App\Core\Enums\SpeedUnits;
  use App\Core\Enums\TemperatureUnits;
  use App\External\OpenWeatherApi;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  class OpenWeatherApiController {
    /**
     * @throws Exception
     */
    #[Query]
    #[Logged]
    // TODO: Handle output conversion
    public static function weather(#[InjectUser] Account $currentAccount, int $locationId, ?TemperatureUnits $temperatureUnits, ?SpeedUnits $speedUnits, ?PressureUnits $pressureUnits): Weather {
      $weather = OpenWeatherApi::getWeather($locationId);

      //      $weather->convertTemperature($temperatureUnits ?? TemperatureUnits::CELSIUS);
      //      $weather->convertSpeed($speedUnits ?? SpeedUnits::METERS_PER_SECOND);
      //      $weather->convertPressure($pressureUnits ?? PressureUnits::HECTOPASCAL);

      return $weather;
    }
  }
}
