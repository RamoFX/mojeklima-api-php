<?php



namespace App\Resources\Location\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class UpdateLocationInput {
    #[Field(outputType: "ID")]
    public int $id;

    #[Field]
    public ?string $cityName;

    #[Field]
    public ?string $countryName;

    #[Field]
    public ?string $label;

    #[Field]
    public ?float $latitude;

    #[Field]
    public ?float $longitude;
  }
}
