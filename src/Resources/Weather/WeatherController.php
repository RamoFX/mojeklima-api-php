<?php



namespace App\Resources\Weather {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Common\Utilities\GlobalProxy;
  use App\Resources\Weather\Enums\PressureUnits;
  use App\Resources\Weather\Enums\SpeedUnits;
  use App\Resources\Weather\Enums\TemperatureUnits;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  readonly class WeatherController {
    private WeatherService $weatherService;



    /**
     * @throws Exception
     */
    public function __construct() {
      $this->weatherService = GlobalProxy::$container->get(WeatherService::class);
    }



    /**
     * @throws Exception
     */
    #[Query]
    #[Logged]
    public function weather(
      #[InjectUser] AccountEntity $currentAccount,
      int $locationId,
      ?TemperatureUnits $temperatureUnits,
      ?SpeedUnits $speedUnits,
      ?PressureUnits $pressureUnits
    ): WeatherEntity {
      return $this->weatherService->weather(
        $currentAccount,
        $locationId,
        $temperatureUnits,
        $speedUnits,
        $pressureUnits
      );
    }
  }
}
