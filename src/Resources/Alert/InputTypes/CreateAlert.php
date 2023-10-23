<?php



namespace App\Resources\Alert\InputTypes {

  use App\Resources\Alert\Enums\Criteria;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class CreateAlert {
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
