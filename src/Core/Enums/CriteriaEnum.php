<?php



namespace App\Core\Enums {

  use MyCLabs\Enum\Enum;
  use TheCodingMachine\GraphQLite\Annotations\EnumType;


  
  /**
   * @EnumType(name="Criteria")
   */
  class CriteriaEnum extends Enum {
    private const TEMPERATURE = "TEMPERATURE";

    private const FEELS_LIKE = "FEELS_LIKE";

    private const HUMIDITY = "HUMIDITY";

    private const PRESSURE = "PRESSURE";

    private const WIND_SPEED = "WIND_SPEED";

    private const WIND_GUST = "WIND_GUST";

    private const WIND_DIRECTION = "WIND_DIRECTION";

    private const CLOUDINESS = "CLOUDINESS";
  }
}
