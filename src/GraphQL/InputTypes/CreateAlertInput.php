<?php



namespace App\GraphQL\InputTypes {

  use App\Core\Enums\ComparatorEnum;
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
    public ComparatorEnum $comparator;

    /** @Field() */
    public float $value;

    /** @Field() */
    public int $updateFrequency;

    /** @Field() */
    public string $message;
  }
}
