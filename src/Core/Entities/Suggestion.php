<?php



namespace App\Core\Entities {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  /** @Type() */
  class Suggestion {
    private float $latitude;
    private float $longitude;
    private string $formatted;
    private ?string $countryCode;
    private ?string $region;



    public function __construct(float $latitude, float $longitude, string $formatted, ?string $countryCode, ?string $region) {
      $this->latitude = $latitude;
      $this->longitude = $longitude;
      $this->formatted = $formatted;
      $this->countryCode = $countryCode;
      $this->region = $region;
    }



    /** @Field() */
    public function getLatitude(): float {
      return $this->latitude;
    }

    public function setLatitude(float $latitude): Suggestion {
      $this->latitude = $latitude;

      return $this;
    }



    /** @Field() */
    public function getLongitude(): float {
      return $this->longitude;
    }

    public function setLongitude(float $longitude): Suggestion {
      $this->longitude = $longitude;

      return $this;
    }



    /** @Field() */
    public function getFormatted(): string {
      return $this->formatted;
    }

    public function setFormatted(string $formatted): Suggestion {
      $this->formatted = $formatted;

      return $this;
    }



    /** @Field() */
    public function getCountryCode(): ?string {
      return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): Suggestion {
      $this->countryCode = $countryCode;

      return $this;
    }



    /** @Field() */
    public function getRegion(): ?string {
      return $this->region;
    }

    public function setRegion(?string $region): Suggestion {
      $this->region = $region;

      return $this;
    }
  }
}
