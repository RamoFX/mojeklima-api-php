<?php



namespace App\Resources\Location {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  readonly class LocationController {
    public function __construct(
      protected LocationService $locationService
    ) {}



    /**
     * @return LocationEntity[]
     */
    #[Query]
    #[Logged]
    public function locations(#[InjectUser] AccountEntity $currentAccount): array {
      return $this->locationService->locations($currentAccount);
    }



    #[Query]
    #[Logged]
    public function locationsCount(#[InjectUser] AccountEntity $currentAccount): int {
      return $this->locationService->locationsCount($currentAccount);
    }



    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public function location(#[InjectUser] AccountEntity $currentAccount, int $id): LocationEntity {
      return $this->locationService->location($currentAccount, $id);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     */
    #[Mutation]
    #[Logged]
    public function createLocation(#[InjectUser] AccountEntity $currentAccount, string $cityName, string $countryName, ?string $label, float $latitude, float $longitude): LocationEntity {
      return $this->locationService->createLocation($currentAccount, $cityName, $countryName, $label, $latitude, $longitude);
    }



    /**
     * @throws OptimisticLockException
     * @throws GraphQLException
     * @throws ORMException
     * @throws EntityNotFound
     */
    #[Mutation]
    #[Logged]
    public function updateLocation(#[InjectUser] AccountEntity $currentAccount, int $id, ?string $cityName, ?string $countryName, ?string $label, ?float $latitude, ?float $longitude): LocationEntity {
      return $this->locationService->updateLocation($currentAccount, $id, $cityName, $countryName, $label, $latitude, $longitude);
    }



    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public function deleteLocation(#[InjectUser] AccountEntity $currentAccount, int $id): LocationEntity {
      return $this->locationService->deleteLocation($currentAccount, $id);
    }
  }
}
