<?php



namespace App\Resources\Weather {

  use App\Resources\Location\LocationEntity;



  class WeatherFactory {
    public static function toJson(WeatherEntity $weather): string {
      return json_encode([
        'temperature' => $weather->temperature,
        'feelsLike' => $weather->feelsLike,
        'humidity' => $weather->humidity,
        'pressure' => $weather->pressure,
        'windSpeed' => $weather->windSpeed,
        'windGust' => $weather->windGust,
        'windDirection' => $weather->windDirection,
        'cloudiness' => $weather->cloudiness,
        'description' => $weather->description,
        'iconCode' => $weather->iconCode,
        'dateTime' => $weather->dateTime,
        'sunrise' => $weather->sunrise,
        'sunset' => $weather->sunset,
        'timezone' => $weather->timezone
      ]);
    }



    public static function fromJson(string $json, LocationEntity $location): WeatherEntity {
      $jsonParsed = json_decode($json, true);

      return new WeatherEntity(
        $jsonParsed['temperature'],
        $jsonParsed['feelsLike'],
        $jsonParsed['humidity'],
        $jsonParsed['pressure'],
        $jsonParsed['windSpeed'],
        $jsonParsed['windGust'],
        $jsonParsed['windDirection'],
        $jsonParsed['cloudiness'],
        $jsonParsed['description'],
        $jsonParsed['iconCode'],
        $jsonParsed['dateTime'],
        $jsonParsed['sunrise'],
        $jsonParsed['sunset'],
        $jsonParsed['timezone'],
        $location
      );
    }
  }
}
