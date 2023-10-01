<?php



namespace App\Core\Entities {

  use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Type;



  /**
   * @Type()
   */
  class Weather {
    private float $temperature;
    private int $humidity;
    private int $pressure;
    private float $windSpeed;



    public function __construct(float $temperature, int $humidity, int $pressure, float $windSpeed) {
      $this->setTemperature($temperature);
      $this->setHumidity($humidity);
      $this->setPressure($pressure);
      $this->setWindSpeed($windSpeed);
    }



    /** @Field() */
    public function getTemperature(): float {
      return $this->temperature;
    }
    
    public function setTemperature(float $temperature): float {
      return $this->temperature = $temperature;
    }



    /** @Field() */
    public function getHumidity(): int {
      return $this->humidity;
    }

    public function setHumidity(int $humidity): int {
      return $this->humidity = $humidity;
    }



    /** @Field() */
    public function getPressure(): int {
      return $this->pressure;
    }
    
    public function setPressure(int $pressure): int {
      return $this->pressure = $pressure;
    }



    /** @Field() */
    public function getWindSpeed(): float {
      return $this->windSpeed;
    }
    
    public function setWindSpeed(float $windSpeed): float {
      return $this->windSpeed = $windSpeed;
    }
  }
}
