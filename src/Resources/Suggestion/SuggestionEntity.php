<?php



namespace App\Resources\Suggestion {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  #[Type(name: "Suggestion")]
  class SuggestionEntity {
    private float $latitude;
    private float $longitude;
    private string $cityName;
    private string $countryName;

    public function __construct(float $latitude, float $longitude, string $cityName, string $countryName) {
      $this->latitude = $latitude;
      $this->longitude = $longitude;
      $this->cityName = $cityName;
      $this->countryName = $countryName;
    }

    #[Field]
    public function getLatitude(): float {
      return $this->latitude;
    }

    public function setLatitude(float $latitude): SuggestionEntity {
      $this->latitude = $latitude;

      return $this;
    }

    #[Field]
    public function getLongitude(): float {
      return $this->longitude;
    }

    public function setLongitude(float $longitude): SuggestionEntity {
      $this->longitude = $longitude;

      return $this;
    }

    #[Field]
    public function getCityName(): string {
      return $this->cityName;
    }

    public function setCityName(string $cityName): SuggestionEntity {
      $this->cityName = $cityName;

      return $this;
    }

    #[Field]
    public function getCountryName(): ?string {
      return $this->countryName;
    }

    public function setCountryName(?string $countryName): SuggestionEntity {
      $this->countryName = $countryName;

      return $this;
    }
  }
}