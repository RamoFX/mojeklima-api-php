<?php



namespace App\Resources\Weather {

  use App\Resources\Cache\Cacheable;
  use App\Resources\Common\Exceptions\CannotSerialize;
  use App\Resources\Common\JsonDeserializable;
  use App\Resources\Location\LocationEntity;
  use ArgumentCountError;
  use JsonSerializable;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  #[Type(name: "Weather")]
  class WeatherEntity extends JsonDeserializable implements JsonSerializable, Cacheable {
    public function __construct(
      #[Field]
      public float $temperature,
      #[Field]
      public float $feelsLike,
      #[Field]
      public int $humidity,
      #[Field]
      public int $pressure,
      #[Field]
      public float $windSpeed,
      #[Field]
      public ?float $windGust,
      #[Field]
      public int $windDirection,
      #[Field]
      public int $cloudiness,
      #[Field]
      public string $description,
      #[Field]
      public string $iconCode,
      #[Field]
      public int $dateTime,
      #[Field]
      public int $sunrise,
      #[Field]
      public int $sunset,
      #[Field]
      public int $timezone,
      #[Field]
      public LocationEntity $location
    ) {}



    /**
     * @throws GraphQLException
     */
    public static function getKey(string ...$components): string {
      if (count($components) !== 2) {
        throw new ArgumentCountError('', 500);
      }

      $latitude = $components[0];
      $longitude = $components[1];

      return "Weather#$latitude,$longitude";
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
        throw new CannotSerialize('Weather');
      }

      return $encoded;
    }
  }
}
