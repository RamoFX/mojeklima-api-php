<?php



namespace App\Resources\Account\DTO {

    use App\Resources\Account\Enums\PressureUnits;
    use App\Resources\Account\Enums\SpeedUnits;
    use App\Resources\Account\Enums\TemperatureUnits;
    use TheCodingMachine\GraphQLite\Annotations\Field;
  use TheCodingMachine\GraphQLite\Annotations\Input;



  #[Input]
  class UpdateAccountInput {
    #[Field]
    public ?string $name;

    #[Field]
    public ?string $email;

    #[Field]
    public ?string $password;

    #[Field]
    public ?TemperatureUnits $temperatureUnits;

    #[Field]
    public ?SpeedUnits $speedUnits;

    #[Field]
    public ?PressureUnits $pressureUnits;
  }
}
