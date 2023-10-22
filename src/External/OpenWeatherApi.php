<?php



namespace App\External {

  use App\Core\Entities\Location;
  use App\Core\Entities\Weather;
  use App\Core\EntityManagerProxy;
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
      $cacheKey = Weather::getKey(strval($latitude), strval($longitude));
      $cacheExpiration = Weather::getExpiration();
      $cachedWeather = RedisProxy::$redis->get($cacheKey);

      if ($cachedWeather) {
        $weather = Weather::jsonDeserialize($cachedWeather);
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

      $weather = (new Weather)
        ->setTemperature($data["main"]["temp"])
        ->setFeelsLike($data["main"]["feels_like"])
        ->setHumidity($data["main"]["humidity"])
        ->setPressure($data["main"]["pressure"])
        ->setWindSpeed($data["wind"]["speed"])
        ->setWindGust($data["wind"]["gust"])
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
      RedisProxy::$redis->set($cacheKey, $cacheValue, 'EX', $cacheExpiration);

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
