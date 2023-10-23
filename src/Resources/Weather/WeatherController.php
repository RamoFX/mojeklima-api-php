<?php



namespace App\Resources\Weather {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Weather\Enums\PressureUnits;
  use App\Resources\Weather\Enums\SpeedUnits;
  use App\Resources\Weather\Enums\TemperatureUnits;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  class WeatherController {
    /**
     * @throws Exception
     */
    #[Query]
    #[Logged]
    // TODO: Handle output conversion
    public static function weather(#[InjectUser] AccountEntity $currentAccount, int $locationId, ?TemperatureUnits $temperatureUnits, ?SpeedUnits $speedUnits, ?PressureUnits $pressureUnits): WeatherEntity {
      $weather = WeatherService::getWeather($locationId);

      //      $weather->convertTemperature($temperatureUnits ?? TemperatureUnits::CELSIUS);
      //      $weather->convertSpeed($speedUnits ?? SpeedUnits::METERS_PER_SECOND);
      //      $weather->convertPressure($pressureUnits ?? PressureUnits::HECTOPASCAL);

      return $weather;
    }
  }
}
