<?php



namespace App\Resources\System {

  use App\Resources\Permission\Enums\Permission;
  use App\Resources\System\DTO\HealthOutput;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Annotations\Right;



  readonly class SystemController {
    public function __construct(
      protected SystemService $systemService
    ) {}



    #[Query]
    public function health(): HealthOutput {
      return $this->systemService->health();
    }



    #[Query]
    #[Logged]
    #[Right(Permission::ONLY_TRUSTED)]
    public function dummyQuery(): void {}



    #[Mutation]
    #[Logged]
    #[Right(Permission::ACCOUNT_MANAGEMENT)]
    public function dummyMutation(): void {}
  }
}
