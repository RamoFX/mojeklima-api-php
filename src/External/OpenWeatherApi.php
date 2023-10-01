<?php



namespace App\External {

  use App\Core\Entities\Weather;
  use RestClient;
  use Exception;



  class OpenWeatherApi {
    public static function getWeather(float $latitude, float $longitude): Weather {
      $api = self::getRestClient();
      $apiKey = self::getApiKey();

      // "https://api.openweathermap.org/data/2.5/weather?appid=$apiKey&lat=$latitude&lon=$longitude&units=metric";
      $result = $api->get("weather", [
        'appid' => $apiKey,
        'lat' => $latitude,
        'lon' => $longitude,
        'units' => 'metric'
      ]);

      if ($result->error)
        throw new Exception($result->error);

      $data = $result->decode_response();

      return new Weather(
        $data["main"]["temp"],
        $data["main"]["humidity"],
        $data["main"]["pressure"],
        $data["wind"]["speed"]
      );
    }



    private static function getRestClient(): RestClient {
      $api = new RestClient([
        'base_url' => "https://api.openweathermap.org/data/2.5/"
      ]);

      $api->register_decoder('json', function($data) {
        return json_decode($data, TRUE);
      });

      return $api;
    }

    private static function getApiKey() {
      return $_ENV["OPEN_WEATHER_API_KEY"];
    }
  }
}
