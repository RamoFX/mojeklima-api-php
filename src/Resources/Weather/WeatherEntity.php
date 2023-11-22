<?php



namespace App\Resources\Weather {

  use App\Resources\Location\LocationEntity;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  #[Type(name: "Weather")]
  class WeatherEntity {
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
  }
}
