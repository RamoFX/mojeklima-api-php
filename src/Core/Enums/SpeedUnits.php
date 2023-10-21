<?php



namespace App\Core\Enums {

  enum SpeedUnits: string {
    case METERS_PER_SECOND = "METERS_PER_SECOND";
    case KILOMETERS_PER_HOUR = "KILOMETERS_PER_HOUR";
    case MILES_PER_HOUR = "MILES_PER_HOUR";
    case KNOTS = "KNOTS";
  }
}
