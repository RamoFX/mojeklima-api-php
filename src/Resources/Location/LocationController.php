<?php



namespace App\Resources\Location {

  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Location\InputTypes\CreateLocationInput;
  use App\Resources\Location\InputTypes\DeleteLocationInput;
  use App\Resources\Location\InputTypes\LocationInput;
  use App\Resources\Location\InputTypes\UpdateLocationInput;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\NoResultException;
  use Doctrine\ORM\OptimisticLockException;
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
    public function locations(): array {
      return $this->locationService->locations();
    }



    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Query]
    #[Logged]
    public function locationsCount(): int {
      return $this->locationService->locationsCount();
    }



    /**
     * @param LocationInput $location
     * @return LocationEntity
     * @throws EntityNotFound
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    #[Query]
    #[Logged]
    public function location(LocationInput $location): LocationEntity {
      return $this->locationService->location($location);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     */
    #[Mutation]
    #[Logged]
    public function createLocation(CreateLocationInput $createLocation): LocationEntity {
      return $this->locationService->createLocation($createLocation);
    }



    /**
     * @throws OptimisticLockException
     * @throws GraphQLException
     * @throws ORMException
     * @throws EntityNotFound
     */
    #[Mutation]
    #[Logged]
    public function updateLocation(UpdateLocationInput $updateLocation): LocationEntity {
      return $this->locationService->updateLocation($updateLocation);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public function deleteLocation(DeleteLocationInput $deleteLocation): LocationEntity {
      return $this->locationService->deleteLocation($deleteLocation);
    }
  }
}
