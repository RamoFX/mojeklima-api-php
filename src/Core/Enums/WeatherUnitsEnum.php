<?php



namespace App\Core\Enums {

  use MyCLabs\Enum\Enum;
  use TheCodingMachine\GraphQLite\Annotations\EnumType;


  
  /**
   * @EnumType(name="WeatherUnits")
   */
  class WeatherUnitsEnum extends Enum {
    private const CELSIUS = "CELSIUS";

    private const FAHRENHEIT = "FAHRENHEIT";

    private const KELVIN = "KELVIN";

    private const RANKINE = "RANKINE";

    private const METERS_PER_SECOND = "METERS_PER_SECOND";

    private const KILOMETERS_PER_HOUR = "KILOMETERS_PER_HOUR";

    private const MILES_PER_HOUR = "MILES_PER_HOUR";

    private const KNOTS = "KNOTS";

    private const HECTOPASCAL = "HECTOPASCAL";

    private const MILLIBAR = "MILLIBAR";

    private const INCHES_OF_MERCURY = "INCHES_OF_MERCURY";
  }
}
