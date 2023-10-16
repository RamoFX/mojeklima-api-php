<?php



namespace App\Core\Enums {

  use MyCLabs\Enum\Enum;
  use TheCodingMachine\GraphQLite\Annotations\EnumType;


  
  /**
   * @EnumType(name="SpeedUnits")
   */
  class SpeedUnitsEnum extends Enum {
    private const METERS_PER_SECOND = "METERS_PER_SECOND";

    private const KILOMETERS_PER_HOUR = "KILOMETERS_PER_HOUR";

    private const MILES_PER_HOUR = "MILES_PER_HOUR";

    private const KNOTS = "KNOTS";
  }
}
