<?php



namespace App\Utilities {

  use App\Core\Enums\PressureUnits;
  use App\Core\Enums\SpeedUnits;
  use App\Core\Enums\TemperatureUnits;
  use Exception;



  class UnitsConverter {
    const defaultMetric = [TemperatureUnits::CELSIUS, SpeedUnits::METERS_PER_SECOND, PressureUnits::HECTOPASCAL];

    /**
     * @throws Exception
     */
    public static function fromMetric(float $value, TemperatureUnits|SpeedUnits|PressureUnits $toUnits): float {
      if (in_array($toUnits, self::defaultMetric)) {
        return $value;
      }

      switch ($toUnits) {
        // temperature - from CELSIUS
        case TemperatureUnits::KELVIN:
          return round($value + 273.15, 2);

        case TemperatureUnits::FAHRENHEIT:
          return round(($value * 9 / 5) + 32, 2);

        case TemperatureUnits::RANKINE:
          return round($value * 9 / 5 + 491.67, 2);

        // speed - from METERS_PER_SECOND
        case SpeedUnits::KILOMETERS_PER_HOUR:
          return round($value * 3.6, 2);

        case SpeedUnits::MILES_PER_HOUR:
          return round($value * 2.23694, 2);

        case SpeedUnits::KNOTS:
          return round($value * 1.9438452, 2);

        // pressure - from HECTOPASCAL
        case PressureUnits::MILLIBAR:
          return round($value, 2);

        case PressureUnits::INCHES_OF_MERCURY:
          return round($value / 33.864, 2);
      }

      $language = Translation::get_preferred_language();
      $messages = [
        "cs" => "Převod na jednotky $toUnits->value není podporován",
        "en" => "Conversion to $toUnits->value is not supported",
        "de" => "Die Konvertierung zu $toUnits->value wird nicht unterstützt",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      throw new Exception($translatedMessage);
    }

    /**
     * @throws Exception
     */
    public static function toMetric(float $value, TemperatureUnits|SpeedUnits|PressureUnits $fromUnits): float {
      if (in_array($fromUnits, self::defaultMetric)) {
        return $value;
      }

      switch ($fromUnits) {
        // temperature - to CELSIUS
        case TemperatureUnits::KELVIN:
          return round($value - 273.15, 2);

        case TemperatureUnits::FAHRENHEIT:
          return round(($value - 32) * 5 / 9, 2);

        case TemperatureUnits::RANKINE:
          return round(($value - 491.67) * 5 / 9, 2);

        // speed - to METERS_PER_SECOND
        case SpeedUnits::KILOMETERS_PER_HOUR:
          return round($value / 3.6, 2);

        case SpeedUnits::MILES_PER_HOUR:
          return round($value / 2.23694, 2);

        case SpeedUnits::KNOTS:
          return round($value / 1.9438452, 2);

        // pressure - to FROM HECTOPASCALS
        case PressureUnits::MILLIBAR:
          return round($value, 2);

        case PressureUnits::INCHES_OF_MERCURY:
          return round($value * 33.864, 2);
      }

      $language = Translation::get_preferred_language();
      $messages = [
        "cs" => "Převod na jednotky $fromUnits->value není podporován",
        "en" => "Conversion to $fromUnits->value is not supported",
        "de" => "Die Konvertierung zu $fromUnits->value wird nicht unterstützt",
      ];
      $translatedMessage = Translation::translate($messages, $language);

      throw new Exception($translatedMessage);
    }
  }
}
