<?php



namespace App\Resources\Alert\DTO {

  use App\Resources\Alert\Enums\Criteria;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;
  use TheCodingMachine\GraphQLite\Types\ID;



  #[Input]
  class CreateAlertInput {
    #[Field]
    public ID $locationId;

    #[Field]
    public bool $isEnabled;

    #[Field]
    public Criteria $criteria;

    #[Field]
    public float $rangeFrom;

    #[Field]
    public float $rangeTo;

    #[Field]
    public int $updateFrequency;

    #[Field]
    public string $message;
  }
}
