<?php



namespace App\Resources\Location {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Common\Utilities\GlobalProxy;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class LocationService {
    /**
     * @param AccountEntity $currentAccount
     * @return LocationEntity[]
     */
    public function locations(AccountEntity $currentAccount): array {
      return $currentAccount->getLocations();
    }



    public function locationsCount(AccountEntity $currentAccount): int {
      return count($this->locations($currentAccount));
    }



    /**
     * @throws EntityNotFound
     */
    public function location(AccountEntity $currentAccount, int $id): LocationEntity {
      $locations = $this->locations($currentAccount);

      foreach ($locations as $location) {
        if ($location->getId() === $id)
          return $location;
      }

      throw new EntityNotFound("Location");
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     */
    public function createLocation(AccountEntity $currentAccount, string $cityName, string $countryName, ?string $label, float $latitude, float $longitude): LocationEntity {
      $newLocation = new LocationEntity($cityName, $countryName, $label, $latitude, $longitude);
      $currentAccount->addLocation($newLocation);

      GlobalProxy::$entityManager->persist($newLocation);
      GlobalProxy::$entityManager->flush($newLocation);

      return $newLocation;
    }



    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws GraphQLException
     * @throws ORMException
     */
    public function updateLocation(AccountEntity $currentAccount, int $id, ?string $cityName, ?string $countryName, ?string $label, ?float $latitude, ?float $longitude): LocationEntity {
      $outdatedLocation = self::location($currentAccount, $id);

      if ($cityName !== null)
        $outdatedLocation->setCityName($cityName);

      if ($countryName !== null)
        $outdatedLocation->setCountryName($countryName);

      if ($label !== null)
        $outdatedLocation->setLabel($label);

      if ($latitude !== null)
        $outdatedLocation->setLatitude($latitude);

      if ($longitude !== null)
        $outdatedLocation->setLongitude($longitude);

      GlobalProxy::$entityManager->flush($outdatedLocation);

      return $outdatedLocation;
    }



    /**
     * @throws EntityNotFound
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function deleteLocation(AccountEntity $currentAccount, int $id): LocationEntity {
      $location = self::location($currentAccount, $id);

      GlobalProxy::$entityManager->remove($location);
      GlobalProxy::$entityManager->flush($location);

      return $location;
    }
  }
}
