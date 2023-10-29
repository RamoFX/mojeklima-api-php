<?php



namespace App\Resources\Weather {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Common\Utilities\GlobalProxy;
  use App\Resources\Common\Utilities\Translation;
  use App\Resources\Location\LocationService;
  use App\Resources\Weather\Enums\PressureUnits;
  use App\Resources\Weather\Enums\SpeedUnits;
  use App\Resources\Weather\Enums\TemperatureUnits;
  use Exception;
  use RestClient;
  use RestClientException;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class WeatherService {
    public function __construct(
      protected LocationService $locationService
    ) {}



    private static function getRestClient(): RestClient {
      $api = new RestClient([
        'base_url' => "https://api.openweathermap.org/data/2.5/"
      ]);

      $api->register_decoder('json', function($data) {
        return json_decode($data, true);
      });

      return $api;
    }



    /**
     * @throws RestClientException
     * @throws Exception
     * @throws GraphQLException
     */
    public function weather(
      AccountEntity $currentAccount,
      int $locationId,
      ?TemperatureUnits $temperatureUnits,
      ?SpeedUnits $speedUnits,
      ?PressureUnits $pressureUnits
    ) {
      // TODO: Handle output conversion
      $location = $this->locationService->location($currentAccount, $locationId);
      $latitude = $location->getLatitude();
      $longitude = $location->getLongitude();

      // language
      $language = Translation::getPreferredLanguage();
      // Open Weather API inconsistency: API supports both "en" (language code) and "cz" (country code).
      // For that reason additional mapping needs to be done
      $languageMap = [
        'cs' => 'cz'
      ];

      // check cache
      $cacheKey = WeatherEntity::getKey(strval($latitude), strval($longitude));
      $cacheExpiration = WeatherEntity::getExpiration();
      $cachedValue = GlobalProxy::$redis->get($cacheKey);

      if ($cachedValue) {
        $weather = WeatherEntity::jsonDeserialize($cachedValue);
        $weather->setLocation($location);

        return $weather;
      }

      // api call
      $api = self::getRestClient();
      $apiKey = $_ENV["OPEN_WEATHER_API_KEY"];

      $result = $api->get("weather", [
        'appid' => $apiKey,
        'lat' => $latitude,
        'lon' => $longitude,
        'units' => 'metric',
        'lang' => $languageMap[$language] ?? $language
      ]);

      if ($result->error)
        throw new Exception($result->error);

      $data = $result->decode_response();

      $weather = (new WeatherEntity())
        ->setTemperature($data["main"]["temp"])
        ->setFeelsLike($data["main"]["feels_like"])
        ->setHumidity($data["main"]["humidity"])
        ->setPressure($data["main"]["pressure"])
        ->setWindSpeed($data["wind"]["speed"])
        ->setWindGust($data["wind"]["gust"] ?? null)
        ->setWindDirection($data["wind"]["deg"])
        ->setCloudiness($data["clouds"]["all"])
        ->setDescription($data["weather"][0]["description"])
        ->setIconCode($data["weather"][0]["icon"])
        ->setDateTime($data["dt"])
        ->setSunrise($data["sys"]["sunrise"])
        ->setSunset($data["sys"]["sunset"])
        ->setTimezone($data["timezone"])
        ->setLocation($location);

      // set cache
      $cacheValue = $weather->jsonSerialize();
      GlobalProxy::$redis->set($cacheKey, $cacheValue, ['EX' => $cacheExpiration]);

      return $weather;
    }
  }
}
