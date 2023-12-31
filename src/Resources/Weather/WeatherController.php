<?php



namespace App\Resources\Weather {

  use App\Resources\Weather\DTO\WeatherInput;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  readonly class WeatherController {
    public function __construct(
      protected WeatherService $weatherService
    ) {}



    /**
     * @throws Exception
     */
    #[Query]
    #[Logged]
    public function weather(WeatherInput $weather): WeatherEntity {
      return $this->weatherService->weather($weather);
    }
  }
}
