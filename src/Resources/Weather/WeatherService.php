<?php



namespace App\Resources\Weather {

  use App\Resources\Common\CommonService;
  use App\Resources\Common\Utilities\ConfigManager;
  use App\Resources\Common\Utilities\Translation;
  use App\Resources\Location\DTO\LocationInput;
  use App\Resources\Location\LocationEntity;
  use App\Resources\Location\LocationService;
  use App\Resources\Weather\DTO\WeatherInput;
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
     * @throws Exception
     * @throws GraphQLException
     */
    public function weather(WeatherInput $weather): WeatherEntity {
      $locationInput = new LocationInput($weather->locationId);
      $location = $this->locationService->location($locationInput);

      return $this->weatherFromLocation($location);
    }



    /**
     * @throws RestClientException
     * @throws Exception
     */
    public function weatherFromLocation(LocationEntity $location): WeatherEntity {
      $language = Translation::getPreferredLanguage();
      // Open Weather API inconsistency: API supports both "en" (language code) and "cz" (country code).
      // For that reason additional mapping needs to be done
      $languageMap = [
        'cs' => 'cz'
      ];

      // check cache
      $weather = $this->getCachedWeather($location);

      if ($weather !== null) {
        return $this->weatherConverter->convert($weather);
      }

      // miss, retrieve from external source
      $apiKey = $this->config->get('keys.api.openWeather');
      $responseJson = self::getRestClient()->get('weather', [
        'appid' => $apiKey,
        'lat' => $location->getLatitude(),
        'lon' => $location->getLongitude(),
        'units' => 'metric',
        'lang' => $languageMap[$language] ?? $language
      ]);

      if ($responseJson->error)
        throw new Exception($responseJson->error);

      $response = $responseJson->decode_response();

      $weather = new WeatherEntity(
        $response['main']['temp'],
        $response['main']['feels_like'],
        $response['main']['humidity'],
        $response['main']['pressure'],
        $response['wind']['speed'],
        $response['wind']['gust'] ?? null,
        $response['wind']['deg'],
        $response['clouds']['all'],
        $response['weather'][0]['description'],
        $response['weather'][0]['icon'],
        $response['dt'],
        $response['sys']['sunrise'],
        $response['sys']['sunset'],
        $response['timezone'],
        $location
      );

      // cache it
      $this->cacheWeather($weather);
      $this->weatherConverter->convert($weather);

      return $weather;
    }



    private function getCachedWeather(LocationEntity $location): WeatherEntity|null {
      try {
        $key = $this->getCacheKeyFromLocation($location);
        $weatherJson = $this->cache->get($key);

        if ($weatherJson === null)
          return null;

        return WeatherFactory::fromJson($weatherJson, $location);
      } catch (InvalidArgumentException) {
        return null;
      }
    }



    private function cacheWeather(WeatherEntity $weather): void {
      try {
        $key = $this->getCacheKeyFromWeather($weather);
        $weatherJson = WeatherFactory::toJson($weather);

        $this->cache->set($key, $weatherJson, 60 * 10);
      } catch (InvalidArgumentException) {
      }
    }



    private function getCacheKeyFromWeather(WeatherEntity $weather): string {
      return $this->getCacheKeyFromLocation($weather->location);
    }



    private function getCacheKeyFromLocation(LocationEntity $location): string {
      return $this->getWeatherCacheKey(
        $location->getLatitude(),
        $location->getLongitude()
      );
    }



    private function getWeatherCacheKey(float $latitude, float $longitude): string {
      return "Weather#$latitude,$longitude";
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
