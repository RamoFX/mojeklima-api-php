<?php



namespace App\GraphQL\InputTypes {

  use App\Core\Enums\CriteriaEnum;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  /** @Input() */
  class CreateAlertInput {
    /** @Field() */
    public bool $isEnabled;

    /** @Field() */
    public CriteriaEnum $criteria;

    /** @Field() */
    public float $rangeFrom;

    /** @Field() */
    public float $rangeTo;

    /** @Field() */
    public int $updateFrequency;

    /** @Field() */
    public string $message;
  }
}
