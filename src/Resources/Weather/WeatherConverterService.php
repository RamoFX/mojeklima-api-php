<?php



namespace App\Resources\Weather {

  use App\Resources\Common\CommonService;
  use App\Resources\Common\Utilities\UnitsConverter;
  use Exception;



  class WeatherConverterService extends CommonService {
    /**
     * @throws Exception
     */
    public function convert(WeatherEntity $weather): WeatherEntity {
      // temperature
      $weather->setTemperature(
        UnitsConverter::fromMetric(
          $weather->getTemperature(),
          $this->currentAccount->getTemperatureUnits()
        )
      );

      $weather->setFeelsLike(
        UnitsConverter::fromMetric(
          $weather->getFeelsLike(),
          $this->currentAccount->getTemperatureUnits()
        )
      );

      // speed
      $weather->setWindSpeed(
        UnitsConverter::fromMetric(
          $weather->getWindSpeed(),
          $this->currentAccount->getSpeedUnits()
        )
      );

      $weather->setWindGust(
        UnitsConverter::fromMetric(
          $weather->getWindGust(),
          $this->currentAccount->getSpeedUnits()
        )
      );

      // pressure
      $weather->setPressure(
        UnitsConverter::fromMetric(
          $weather->getPressure(),
          $this->currentAccount->getPressureUnits()
        )
      );

      return $weather;
    }
  }
}
