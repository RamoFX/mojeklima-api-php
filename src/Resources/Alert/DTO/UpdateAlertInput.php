<?php



namespace App\Resources\Alert\DTO {

  use App\Resources\Alert\Enums\Criteria;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class UpdateAlertInput {
    #[Field(outputType: "ID")]
    public int $id;

    #[Field]
    public ?bool $isEnabled;

    #[Field]
    public ?Criteria $criteria;

    #[Field]
    public ?float $rangeFrom;

    #[Field]
    public ?float $rangeTo;

    #[Field]
    public ?int $updateFrequency;

    #[Field]
    public ?string $message;
  }
}
