<?php



namespace App\External {

  use App\Core\Entities\Location;
  use App\Core\Entities\Weather;
  use App\Core\EntityManagerProxy;
  use App\GraphQL\DevelopmentOutputBuffer;
  use App\GraphQL\Proxies\RedisProxy;
  use App\Utilities\Translation;
  use Exception;
  use RestClient;
  use RestClientException;



  class OpenWeatherApi {
    /**
     * @throws RestClientException
     * @throws Exception
     */
    public static function getWeather(int $locationId): Weather {
      // language
      $language = Translation::get_preferred_language();
      $languageMap = [
        'cs' => 'cz'
      ];

      // location
      $location = EntityManagerProxy::$entity_manager->find(Location::class, $locationId);
      $latitude = $location->getLatitude();
      $longitude = $location->getLongitude();

      // check cache
      $cacheKey = Weather::createKey($latitude, $longitude);
      $cachedWeather = RedisProxy::$redis->get($cacheKey);

      if ($cachedWeather) {
        DevelopmentOutputBuffer::set('weather retrieved from cache', true);
        $weather = Weather::fromJson($cachedWeather);
        $weather->setLocation($location);

        return $weather;
      }

      // api call
      $api = self::get_rest_client();
      $apiKey = self::get_api_key();

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

      $weather = new Weather(
        $data["main"]["temp"],
        $data["main"]["feels_like"],
        $data["main"]["humidity"],
        $data["main"]["pressure"],
        $data["wind"]["speed"],
        $data["wind"]["gust"],
        $data["wind"]["deg"],
        $data["clouds"]["all"],
        $data["weather"][0]["description"],
        $data["weather"][0]["icon"],
        $data["dt"],
        $data["sys"]["sunrise"],
        $data["sys"]["sunset"],
        $data["timezone"]
      );

      $weather->setLocation($location);

      // set cache
      $weatherJson = $weather->toJson();
      RedisProxy::$redis->set($cacheKey, $weatherJson, 'EX', 600);
      DevelopmentOutputBuffer::set('weather retrieved from cache', false);

      return $weather;
    }

    private static function get_rest_client(): RestClient {
      $api = new RestClient([
        'base_url' => "https://api.openweathermap.org/data/2.5/"
      ]);

      $api->register_decoder('json', function($data) {
        return json_decode($data, true);
      });

      return $api;
    }

    private static function get_api_key() {
      return $_ENV["OPEN_WEATHER_API_KEY"];
    }
  }
}
