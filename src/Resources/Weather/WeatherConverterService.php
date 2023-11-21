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
      $weather->temperature = UnitsConverter::fromMetric(
        $weather->temperature,
        $this->currentAccount->getTemperatureUnits()
      );

      $weather->feelsLike = UnitsConverter::fromMetric(
        $weather->feelsLike,
        $this->currentAccount->getTemperatureUnits()
      );

      $weather->windSpeed = UnitsConverter::fromMetric(
        $weather->windSpeed,
        $this->currentAccount->getSpeedUnits()
      );

      $weather->windGust = UnitsConverter::fromMetric(
        $weather->windGust,
        $this->currentAccount->getSpeedUnits()
      );

      $weather->pressure = UnitsConverter::fromMetric(
        $weather->pressure,
        $this->currentAccount->getPressureUnits()
      );

      return $weather;
    }
  }
}
