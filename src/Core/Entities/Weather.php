<?php



namespace App\Core\Entities {

  use App\Core\Validator;
  use App\Utilities\UnitsConverter;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  /**
   * @Type()
   */
  class Weather {
    private float $temperature;
    private float $feelsLike;
    private int $humidity;
    private int $pressure;
    private float $windSpeed;
    private float $windGust;
    private int $windDirection;
    private int $cloudiness;
    private string $description;
    private string $iconCode;
    private int $sunrise;
    private int $sunset;
    private int $timezone;



    public function __construct(
      float $temperature,
      float $feelsLike,
      int $humidity,
      int $pressure,
      float $windSpeed,
      float $windGust,
      int $windDirection,
      int $cloudiness,
      string $description,
      string $iconCode,
      int $sunrise,
      int $sunset,
      int $timezone
    ) {
      $this->setTemperature($temperature);
      $this->setFeelsLike($feelsLike);
      $this->setHumidity($humidity);
      $this->setPressure($pressure);
      $this->setWindSpeed($windSpeed);
      $this->setWindGust($windGust);
      $this->setWindDirection($windDirection);
      $this->setCloudiness($cloudiness);
      $this->setDescription($description);
      $this->setIconCode($iconCode);
      $this->setSunrise($sunrise);
      $this->setSunset($sunset);
      $this->setTimezone($timezone);
    }



    public function convertTemperature(string $toUnits) {
      Validator::oneOf("toUnits", $toUnits, [ "CELSIUS", "FAHRENHEIT", "KELVIN", "RANKINE" ]);

      $this->setTemperature(
        UnitsConverter::fromMetric(
          $this->getTemperature(),
          $toUnits
        )
      );

      $this->setFeelsLike(
        UnitsConverter::fromMetric(
          $this->getFeelsLike(),
          $toUnits
        )
      );
    }



    public function convertSpeed(string $toUnits) {
      Validator::oneOf("toUnits", $toUnits, [ "METERS_PER_SECOND", "KILOMETERS_PER_HOUR", "MILES_PER_HOUR", "KNOTS" ]);

      $this->setWindSpeed(
        UnitsConverter::fromMetric(
          $this->getWindSpeed(),
          $toUnits
        )
      );

      $this->setWindGust(
        UnitsConverter::fromMetric(
          $this->getWindGust(),
          $toUnits
        )
      );
    }



    public function convertPressure(string $toUnits) {
      Validator::oneOf("toUnits", $toUnits, [ "HECTOPASCAL", "MILLIBAR", "INCHES_OF_MERCURY" ]);
      
      $this->setPressure(
        UnitsConverter::fromMetric(
          $this->getPressure(),
          $toUnits
        )
      );
    }



    /** @Field() */
    public function getTemperature(): float {
      return $this->temperature;
    }
    
    public function setTemperature(float $temperature): float {
      return $this->temperature = $temperature;
    }



    /** @Field() */
    public function getFeelsLike(): float {
      return $this->feelsLike;
    }
    
    public function setFeelsLike(float $feelsLike): float {
      return $this->feelsLike = $feelsLike;
    }



    /** @Field() */
    public function getHumidity(): int {
      return $this->humidity;
    }

    public function setHumidity(int $humidity): int {
      return $this->humidity = $humidity;
    }



    /** @Field() */
    public function getPressure(): int {
      return $this->pressure;
    }
    
    public function setPressure(int $pressure): int {
      return $this->pressure = $pressure;
    }



    /** @Field() */
    public function getWindSpeed(): float {
      return $this->windSpeed;
    }

    public function setWindSpeed(float $windSpeed): float {
      return $this->windSpeed = $windSpeed;
    }



    /** @Field() */
    public function getWindGust(): float {
      return $this->windGust;
    }

    public function setWindGust(float $windGust): float {
      return $this->windGust = $windGust;
    }



    /** @Field() */
    public function getWindDirection(): int {
      return $this->windDirection;
    }

    public function setWindDirection(int $windDirection): int {
      return $this->windDirection = $windDirection;
    }



    /** @Field() */
    public function getCloudiness(): int {
      return $this->cloudiness;
    }

    public function setCloudiness(int $cloudiness): int {
      return $this->cloudiness = $cloudiness;
    }



    /** @Field() */
    public function getDescription(): string {
      return $this->description;
    }

    public function setDescription(string $description): string {
      return $this->description = $description;
    }



    /** @Field() */
    public function getIconCode(): string {
      return $this->iconCode;
    }

    public function setIconCode(string $iconCode): string {
      return $this->iconCode = $iconCode;
    }



    /** @Field() */
    public function getSunrise(): int {
      return $this->sunrise;
    }

    public function setSunrise(int $sunrise): int {
      return $this->sunrise = $sunrise;
    }



    /** @Field() */
    public function getSunset(): int {
      return $this->sunset;
    }

    public function setSunset(int $sunset): int {
      return $this->sunset = $sunset;
    }



    /** @Field() */
    public function getTimezone(): int {
      return $this->timezone;
    }

    public function setTimezone(int $timezone): int {
      return $this->timezone = $timezone;
    }
  }
}
