<?php



namespace App\Resources\Alert {

  use App\Resources\Account\Enums\PressureUnits;
  use App\Resources\Account\Enums\SpeedUnits;
  use App\Resources\Account\Enums\TemperatureUnits;
  use App\Resources\Alert\Enums\Criteria;
  use App\Resources\Alert\Enums\RangeField;
  use App\Resources\Auth\AuthService;
  use App\Resources\Common\CommonService;
  use App\Resources\Common\Enums\ConversionDirection;
  use App\Resources\Common\Utilities\UnitsConverter;
  use Exception;



  class AlertConverterService extends CommonService {
    public function __construct(
      protected AuthService $authService
    ) {
      parent::__construct();
    }



    /**
     * @param AlertEntity[] $alerts
     * @param ConversionDirection $direction
     * @return AlertEntity[]
     * @throws Exception
     */
    public function convertMultipleRanges(array $alerts, ConversionDirection $direction): array {
      foreach ($alerts as $alert) {
        $this->convertRange($alert, $direction);
      }

      return $alerts;
    }



    /**
     * @throws Exception
     */
    public function convertRange(AlertEntity $alert, ConversionDirection $direction): AlertEntity {
      $this->convertRangeField($alert, $direction, RangeField::RANGE_FROM);
      $this->convertRangeField($alert, $direction, RangeField::RANGE_TO);

      return $alert;
    }



    /**
     * @throws Exception
     */
    public function convertRangeField(AlertEntity $alert, ConversionDirection $direction, RangeField $field): AlertEntity {
      $relevantUnits = $this->matchUnits($alert);

      if ($relevantUnits === null)
        return $alert;

      $fieldValue = match ($field) {
        RangeField::RANGE_FROM => $alert->getRangeFrom(),
        RangeField::RANGE_TO => $alert->getRangeTo()
      };

      $convertedValue = match ($direction) {
        ConversionDirection::FROM_METRIC => UnitsConverter::fromMetric($fieldValue, $relevantUnits),
        ConversionDirection::TO_METRIC => UnitsConverter::toMetric($fieldValue, $relevantUnits)
      };

      return match ($field) {
        RangeField::RANGE_FROM => $alert->setRangeFrom($convertedValue),
        RangeField::RANGE_TO => $alert->setRangeTo($convertedValue)
      };
    }



    private function matchUnits(AlertEntity $alert): TemperatureUnits|PressureUnits|SpeedUnits|null {
      return match ($alert->getCriteria()) {
        Criteria::TEMPERATURE,
        Criteria::FEELS_LIKE => $this->currentAccount->getTemperatureUnits(),

        Criteria::PRESSURE => $this->currentAccount->getPressureUnits(),

        Criteria::WIND_SPEED,
        Criteria::WIND_GUST => $this->currentAccount->getSpeedUnits(),

        default => null
      };
    }
  }
}
