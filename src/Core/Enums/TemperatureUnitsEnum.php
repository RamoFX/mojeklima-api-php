<?php



namespace App\Core\Enums {

  use MyCLabs\Enum\Enum;
  use TheCodingMachine\GraphQLite\Annotations\EnumType;


  
  /**
   * @EnumType(name="TemperatureUnits")
   */
  class TemperatureUnitsEnum extends Enum {
    private const CELSIUS = "CELSIUS";

    private const FAHRENHEIT = "FAHRENHEIT";

    private const KELVIN = "KELVIN";

    private const RANKINE = "RANKINE";
  }
}
