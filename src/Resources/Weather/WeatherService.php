<?php



namespace App\Resources\Weather {

  use App\Resources\Common\Utilities\GlobalProxy;
  use App\Resources\Common\Utilities\Translation;
  use App\Resources\Location\LocationEntity;
  use Exception;
  use RestClient;
  use RestClientException;



  // TODO: Isn't this a service of external data source kind?
  class WeatherService {
    /**
     * @throws RestClientException
     * @throws Exception
     */
    public static function getWeather(int $locationId): WeatherEntity {
      // language
      $language = Translation::getPreferredLanguage();
      // Open Weather API inconsistency: API supports both "en" (language code) and "cz" (country code).
      // For that reason mapping needs to be done
      $languageMap = [
        'cs' => 'cz'
      ];

      // location
      $location = GlobalProxy::$entityManager->find(LocationEntity::class, $locationId);
      $latitude = $location->getLatitude();
      $longitude = $location->getLongitude();

      // check cache
      $cacheKey = WeatherEntity::getKey(strval($latitude), strval($longitude));
      $cacheExpiration = WeatherEntity::getExpiration();
      $cachedWeather = GlobalProxy::$redis->get($cacheKey);

      if ($cachedWeather) {
        $weather = WeatherEntity::jsonDeserialize($cachedWeather);
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

      $weather = (new WeatherEntity)
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
      GlobalProxy::$redis->set($cacheKey, $cacheValue, 'EX', $cacheExpiration);

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
