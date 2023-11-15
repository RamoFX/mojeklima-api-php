<?php



namespace App\Resources\System {

  use App\Resources\Permission\Enums\Permission;
  use App\Resources\System\DTO\HealthOutput;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\NoResultException;
  use RedisException;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Annotations\Right;



  readonly class SystemController {
    public function __construct(
      protected SystemService $systemService
    ) {}



    /**
     * @throws NonUniqueResultException
     * @throws RedisException
     * @throws NoResultException
     */
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
