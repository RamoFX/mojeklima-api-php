<?php



namespace App\Resources\Location {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Common\Utilities\GlobalProxy;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class LocationController {
    /**
     * @return LocationEntity[]
     */
    #[Query]
    #[Logged]
    public static function allLocations(#[InjectUser] AccountEntity $currentAccount): array {
      return $currentAccount->getLocations();
    }

    #[Query]
    #[Logged]
    public static function locationsCount(#[InjectUser] AccountEntity $currentAccount): int {
      return count(self::allLocations($currentAccount));
    }

    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public static function location(#[InjectUser] AccountEntity $currentAccount, int $id): LocationEntity {
      $allLocations = self::allLocations($currentAccount);

      foreach ($allLocations as $location) {
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
    #[Mutation]
    #[Logged]
    public static function createLocation(#[InjectUser] AccountEntity $currentAccount, string $cityName, string $countryName, ?string $label, float $latitude, float $longitude): LocationEntity {
      $new_location = new LocationEntity($cityName, $countryName, $label, $latitude, $longitude);
      $currentAccount->addLocation($new_location);

      GlobalProxy::$entityManager->persist($new_location);
      GlobalProxy::$entityManager->flush($new_location);

      return $new_location;
    }

    /**
     * @throws OptimisticLockException
     * @throws GraphQLException
     * @throws ORMException
     * @throws EntityNotFound
     */
    #[Mutation]
    #[Logged]
    public static function updateLocation(#[InjectUser] AccountEntity $currentAccount, int $id, ?string $cityName, ?string $countryName, ?string $label, ?float $latitude, ?float $longitude): LocationEntity {
      $outdated_location = self::location($currentAccount, $id);

      if ($cityName !== null)
        $outdated_location->setCityName($cityName);

      if ($countryName !== null)
        $outdated_location->setCountryName($countryName);

      if ($label !== null)
        $outdated_location->setLabel($label);

      if ($latitude !== null)
        $outdated_location->setLatitude($latitude);

      if ($longitude !== null)
        $outdated_location->setLongitude($longitude);

      GlobalProxy::$entityManager->flush($outdated_location);

      return $outdated_location;
    }

    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public static function deleteLocation(#[InjectUser] AccountEntity $currentAccount, int $id): LocationEntity {
      $location = self::location($currentAccount, $id);

      GlobalProxy::$entityManager->remove($location);
      GlobalProxy::$entityManager->flush($location);

      return $location;
    }
  }
}
