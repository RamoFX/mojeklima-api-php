<?php



namespace App\Core\Entities {

  use App\Core\Cacheable;
  use App\Core\Enums\PressureUnits;
  use App\Core\Enums\SpeedUnits;
  use App\Core\Enums\TemperatureUnits;
  use App\Core\JsonDeserializable;
  use App\Utilities\Translation;
  use App\Utilities\UnitsConverter;
  use Exception;
  use JsonSerializable;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  #[Type]
  class Weather extends JsonDeserializable implements JsonSerializable, Cacheable { // TODO: make cache service?
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



    /**
     * @throws GraphQLException
     */
    public static function getKey(string ...$components): string {
      if (count($components) !== 2) {
        $language = Translation::get_preferred_language(); // TODO: This can be automated
        // TODO: In order to automate sibling logic this can be done differently
        $messages = [
          "cs" => "Weather::getKey přijímá právě dva argumenty (zeměpisná šířka, zeměpisná délka)",
          "en" => "Weather::getKey accepts exactly two arguments (latitude, longitude)",
          "de" => "Weather::getKey akzeptiert genau zwei Argumente (Breitengrad, Längengrad)",
        ];
        $translatedMessage = Translation::translate($messages, $language); // TODO: This can be automated

        throw new GraphQLException($translatedMessage);
      }

      $latitude = $components[0];
      $longitude = $components[1];

      return "Weather:$latitude,$longitude";
    }

    public static function getExpiration(): int {
      return 60 * 10;
    }



    /**
     * @throws GraphQLException
     */
    public function jsonSerialize(): string {
      $fields = get_object_vars($this);
      unset($fields['location']);

      $encoded = json_encode($fields);

      if ($encoded === false) {
        // TODO: make translation service?
        $language = Translation::get_preferred_language(); // TODO: This can be automated
        // TODO: In order to automate sibling logic this can be done differently
        $messages = [
          "cs" => "Nelze serializovat Počasí",
          "en" => "Cannot serialize Weather",
          "de" => "Weather kann nicht serialisiert werden",
        ];
        $translatedMessage = Translation::translate($messages, $language); // TODO: This can be automated

        throw new GraphQLException($translatedMessage);
      }

      return $encoded;
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
