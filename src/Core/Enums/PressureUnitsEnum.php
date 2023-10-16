<?php



namespace App\Core\Enums {

  use MyCLabs\Enum\Enum;
  use TheCodingMachine\GraphQLite\Annotations\EnumType;


  
  /**
   * @EnumType(name="PressureUnits")
   */
  class PressureUnitsEnum extends Enum {
    private const HECTOPASCAL = "HECTOPASCAL";

    private const MILLIBAR = "MILLIBAR";

    private const INCHES_OF_MERCURY = "INCHES_OF_MERCURY";
  }
}
