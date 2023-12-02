<?php



namespace App\Resources\Location\DTO {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;
  use TheCodingMachine\GraphQLite\Types\ID;



  #[Input]
  class UpdateLocationInput {
    #[Field]
    public ID $id;

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
