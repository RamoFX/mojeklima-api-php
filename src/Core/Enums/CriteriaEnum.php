<?php



namespace App\Core\Enums {

  use MyCLabs\Enum\Enum;



  class CriteriaEnum extends Enum {
    private const TEMPERATURE = "TEMPERATURE";

    private const HUMIDITY = "HUMIDITY";

    private const WIND_SPEED = "WIND_SPEED";

    private const PRESSURE = "PRESSURE";
  }
}
