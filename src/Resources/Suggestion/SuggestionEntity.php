<?php



namespace App\Resources\Suggestion {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  #[Type(name: "Suggestion")]
  class SuggestionEntity {
    public function __construct(
      #[Field]
      public float $latitude,
      #[Field]
      public float $longitude,
      #[Field]
      public ?string $cityName,
      #[Field]
      public string $countryName
    ) {}
  }
}
