<?php



namespace App\Resources\Weather\Enums {

  enum TemperatureUnits: string {
    case CELSIUS = "CELSIUS";
    case FAHRENHEIT = "FAHRENHEIT";
    case KELVIN = "KELVIN";
    case RANKINE = "RANKINE";
  }
}
