<?php



namespace App\Utilities {

  use Exception;



  class UnitsConverter {
    public static function fromMetric(float $value, string $toUnits): float {
      if (in_array($toUnits, [ 'CELSIUS', 'METERS_PER_SECOND', 'HECTOPASCAL' ])) {
        return $value;
      }

      switch ($toUnits) {
        // temperature - from CELSIUS
        case 'KELVIN':
          return round($value + 273.15, 2);

        case 'FAHRENHEIT':
          return round(($value * 9/5) + 32, 2);

        case 'RANKINE':
          return round($value * 9/5 + 491.67, 2);

        // speed - from METERS_PER_SECOND
        case 'KILOMETERS_PER_HOUR':
          return round($value * 3.6, 2);

        case 'MILES_PER_HOUR':
          return round($value * 2.23694, 2);

        case 'KNOTS':
          return round($value * 1.9438452, 2);

        // pressure - from HECTOPASCAL
        case 'MILLIBAR':
          return round($value, 2);

        case 'INCHES_OF_MERCURY':
          return round($value / 33.864, 2);
      }

      $language = Translation::get_preferred_language();
      $messages = [
        "cs" => "Převod na jednotky $toUnits není podporován",
        "en" => "Conversion to $toUnits is not supported",
        "de" => "Die Konvertierung zu $toUnits wird nicht unterstützt",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      throw new Exception($translatedMessage);
    }


    
    public static function toMetric(float $value, string $fromUnits): float {
      if (in_array($fromUnits, [ 'CELSIUS', 'METERS_PER_SECOND', 'HECTOPASCAL' ])) {
        return $value;
      }

      switch ($fromUnits) {
        // temperature - to CELSIUS
        case 'KELVIN':
          return round($value - 273.15, 2);

        case 'FAHRENHEIT':
          return round(($value - 32) * 5/9, 2);

        case 'RANKINE':
          return round(($value - 491.67) * 5/9, 2);

        // speed - to METERS_PER_SECOND
        case 'KILOMETERS_PER_HOUR':
          return round($value / 3.6, 2);

        case 'MILES_PER_HOUR':
          return round($value / 2.23694, 2);

        case 'KNOTS':
          return round($value / 1.9438452, 2);

        // pressure - to FROM HECTOPASCALS
        case 'MILLIBAR':
          return round($value, 2);

        case 'INCHES_OF_MERCURY':
          return round($value * 33.864, 2);
      }

      $language = Translation::get_preferred_language();
      $messages = [
        "cs" => "Převod na jednotky $fromUnits není podporován",
        "en" => "Conversion to $fromUnits is not supported",
        "de" => "Die Konvertierung zu $fromUnits wird nicht unterstützt",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      throw new Exception($translatedMessage);
    }
  }
}
