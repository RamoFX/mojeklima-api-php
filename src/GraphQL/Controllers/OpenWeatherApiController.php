<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Weather;
  use App\External\OpenWeatherApi;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  class OpenWeatherApiController {
    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function weather(Account $currentAccount, int $locationId): Weather {
      $location = LocationController::location($currentAccount, $locationId);

      return OpenWeatherApi::getWeather(
        $location->getLatitude(),
        $location->getLongitude()
      );
    }
  }
}
