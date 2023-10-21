<?php



namespace App\GraphQL\InputTypes {

  use App\Core\Enums\Criteria;
  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  /** @Input(name="UpdateAlertInput") */
  class UpdateAlertInput {
    /** @Field() */
    public ?bool $isEnabled;

    /** @Field() */

    /** @Field() */
    public ?Criteria $criteria;
    public ?float $rangeFrom;

    /** @Field() */
    public ?float $rangeTo;

    /** @Field() */
    public ?int $updateFrequency;

    /** @Field() */
    public ?string $message;
  }
}
