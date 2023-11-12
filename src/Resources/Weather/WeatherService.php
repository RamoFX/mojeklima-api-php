<?php



namespace App\Resources\Weather {

  use App\Resources\Common\CommonService;
  use App\Resources\Common\Enums\ConversionDirection;
  use App\Resources\Common\Utilities\ConfigManager;
  use App\Resources\Common\Utilities\Translation;
  use App\Resources\Location\InputTypes\LocationInput;
  use App\Resources\Location\LocationService;
  use App\Resources\Weather\InputTypes\WeatherInput;
  use Exception;
  use Psr\SimpleCache\CacheInterface;
  use Psr\SimpleCache\InvalidArgumentException;
  use RestClient;
  use RestClientException;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class WeatherService extends CommonService {
    public function __construct(
      protected ConfigManager $config,
      protected CacheInterface $cache,
      protected LocationService $locationService,
      protected WeatherConverterService $weatherConverter
    ) {
      parent::__construct();
    }



    /**
     * @throws RestClientException
     * @throws Exception
     * @throws GraphQLException
     * @throws InvalidArgumentException
     */
    public function weather(WeatherInput $weather) {
      $locationInput = new LocationInput();
      $locationInput->id = $weather->locationId;
      $location = $this->locationService->location($locationInput);
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
      $cachedValue = $this->cache->get($cacheKey);

      if ($cachedValue) {
        $weather = WeatherEntity::jsonDeserialize($cachedValue);

        $weather->setLocation($location);
        $this->weatherConverter->convert($weather, ConversionDirection::FROM_METRIC);

        return $weather;
      }

      // api call
      $api = self::getRestClient();
      $apiKey = $this->config->get('keys.api.openWeather');
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
        ->setTemperature($data['main']['temp'])
        ->setFeelsLike($data['main']['feels_like'])
        ->setHumidity($data['main']['humidity'])
        ->setPressure($data['main']['pressure'])
        ->setWindSpeed($data['wind']['speed'])
        ->setWindGust($data['wind']['gust'] ?? null)
        ->setWindDirection($data['wind']['deg'])
        ->setCloudiness($data['clouds']['all'])
        ->setDescription($data['weather'][0]['description'])
        ->setIconCode($data['weather'][0]['icon'])
        ->setDateTime($data['dt'])
        ->setSunrise($data['sys']['sunrise'])
        ->setSunset($data['sys']['sunset'])
        ->setTimezone($data['timezone'])
        ->setLocation($location);

      // with caching
      $cacheValue = $weather->jsonSerialize();

      $this->cache->set($cacheKey, $cacheValue, [ 'EX' => $cacheExpiration ]);
      $this->weatherConverter->convert($weather, ConversionDirection::FROM_METRIC);

      return $weather;
    }



    private static function getRestClient(): RestClient {
      $api = new RestClient([
        'base_url' => "https://api.openweathermap.org/data/2.5/"
      ]);

      $api->register_decoder('json', function($data) {
        return json_decode($data, true);
      });

      return $api;
    }
  }
}
