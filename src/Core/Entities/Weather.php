<?php



namespace App\Core\Entities {

  use App\Core\Enums\PressureUnits;
  use App\Core\Enums\SpeedUnits;
  use App\Core\Enums\TemperatureUnits;
  use App\Utilities\UnitsConverter;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  #[Type]
  class Weather {
    private float $temperature;
    private float $feelsLike;
    private int $humidity;
    private int $pressure;
    private float $windSpeed;
    private ?float $windGust;
    private int $windDirection;
    private int $cloudiness;
    private string $description;
    private string $iconCode;
    private int $dateTime;
    private int $sunrise;
    private int $sunset;
    private int $timezone;
    private Location $location;

    public function __construct(
      float $temperature,
      float $feelsLike,
      int $humidity,
      int $pressure,
      float $windSpeed,
      ?float $windGust,
      int $windDirection,
      int $cloudiness,
      string $description,
      string $iconCode,
      int $dateTime,
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
      $this->setDateTime($dateTime);
      $this->setSunrise($sunrise);
      $this->setSunset($sunset);
      $this->setTimezone($timezone);
    }

    /**
     * @throws GraphQLException
     * @throws Exception
     */
    public function convertTemperature(TemperatureUnits $toUnits): void {
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

    /**
     * @throws GraphQLException
     * @throws Exception
     */
    public function convertSpeed(SpeedUnits $toUnits): void {
      $this->setWindSpeed(
        UnitsConverter::fromMetric(
          $this->getWindSpeed(),
          $toUnits
        )
      );

      if ($this->getWindGust() !== null) {
        $this->setWindGust(
          UnitsConverter::fromMetric(
            $this->getWindGust(),
            $toUnits
          )
        );
      }
    }

    /**
     * @throws GraphQLException
     * @throws Exception
     */
    public function convertPressure(PressureUnits $toUnits): void {
      $this->setPressure(
        UnitsConverter::fromMetric(
          $this->getPressure(),
          $toUnits
        )
      );
    }

    #[Field]
    public function getTemperature(): float {
      return $this->temperature;
    }

    public function setTemperature(float $temperature): Weather {
      $this->temperature = $temperature;

      return $this;
    }

    #[Field]
    public function getFeelsLike(): float {
      return $this->feelsLike;
    }

    public function setFeelsLike(float $feelsLike): Weather {
      $this->feelsLike = $feelsLike;

      return $this;
    }

    #[Field]
    public function getHumidity(): int {
      return $this->humidity;
    }

    public function setHumidity(int $humidity): Weather {
      $this->humidity = $humidity;

      return $this;
    }

    #[Field]
    public function getPressure(): int {
      return $this->pressure;
    }

    public function setPressure(int $pressure): Weather {
      $this->pressure = $pressure;

      return $this;
    }

    #[Field]
    public function getWindSpeed(): float {
      return $this->windSpeed;
    }

    public function setWindSpeed(float $windSpeed): Weather {
      $this->windSpeed = $windSpeed;

      return $this;
    }

    #[Field]
    public function getWindGust(): ?float {
      return $this->windGust;
    }

    public function setWindGust(?float $windGust): Weather {
      $this->windGust = $windGust;

      return $this;
    }

    #[Field]
    public function getWindDirection(): int {
      return $this->windDirection;
    }

    public function setWindDirection(int $windDirection): Weather {
      $this->windDirection = $windDirection;

      return $this;
    }

    #[Field]
    public function getCloudiness(): int {
      return $this->cloudiness;
    }

    public function setCloudiness(int $cloudiness): Weather {
      $this->cloudiness = $cloudiness;

      return $this;
    }

    #[Field]
    public function getDescription(): string {
      return $this->description;
    }

    public function setDescription(string $description): Weather {
      $this->description = $description;

      return $this;
    }

    #[Field]
    public function getIconCode(): string {
      return $this->iconCode;
    }

    public function setIconCode(string $iconCode): Weather {
      $this->iconCode = $iconCode;

      return $this;
    }

    #[Field]
    public function getDateTime(): int {
      return $this->dateTime;
    }

    public function setDateTime(int $dateTime): Weather {
      $this->dateTime = $dateTime;

      return $this;
    }

    #[Field]
    public function getSunrise(): int {
      return $this->sunrise;
    }

    public function setSunrise(int $sunrise): Weather {
      $this->sunrise = $sunrise;

      return $this;
    }

    #[Field]
    public function getSunset(): int {
      return $this->sunset;
    }

    public function setSunset(int $sunset): Weather {
      $this->sunset = $sunset;

      return $this;
    }

    #[Field]
    public function getTimezone(): int {
      return $this->timezone;
    }

    public function setTimezone(int $timezone): Weather {
      $this->timezone = $timezone;

      return $this;
    }

    #[Field]
    public function getLocation(): Location {
      return $this->location;
    }

    public function setLocation(Location $location): Weather {
      $this->location = $location;

      return $this;
    }
  }
}
