<?php



namespace App\Core\Enums {

  enum Criteria: string {
    case TEMPERATURE = "TEMPERATURE";
    case FEELS_LIKE = "FEELS_LIKE";
    case HUMIDITY = "HUMIDITY";
    case PRESSURE = "PRESSURE";
    case WIND_SPEED = "WIND_SPEED";
    case WIND_GUST = "WIND_GUST";
    case WIND_DIRECTION = "WIND_DIRECTION";
    case CLOUDINESS = "CLOUDINESS";
  }
}
