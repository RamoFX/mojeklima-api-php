<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Location;
  use App\Core\EntityManagerProxy;
  use App\GraphQL\Exceptions\EntityNotFound;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class LocationController {
    /**
     * @return Location[]
     */
    #[Query]
    #[Logged]
    public static function allLocations(#[InjectUser] Account $currentAccount): array {
      return $currentAccount->getLocations();
    }

    #[Query]
    #[Logged]
    public static function locationsCount(#[InjectUser] Account $currentAccount): int {
      return count(self::allLocations($currentAccount));
    }

    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public static function location(#[InjectUser] Account $currentAccount, int $id): Location {
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
    public static function createLocation(#[InjectUser] Account $currentAccount, string $cityName, string $countryName, ?string $label, float $latitude, float $longitude): Location {
      $new_location = new Location($cityName, $countryName, $label, $latitude, $longitude);
      $currentAccount->addLocation($new_location);

      EntityManagerProxy::$entity_manager->persist($new_location);
      EntityManagerProxy::$entity_manager->flush($new_location);

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
    public static function updateLocation(#[InjectUser] Account $currentAccount, int $id, ?string $cityName, ?string $countryName, ?string $label, ?float $latitude, ?float $longitude): Location {
      $outdated_location = self::location($currentAccount, $id);

      if ($name !== null)
        $outdated_location->setName($name);

      if ($description !== null)
        $outdated_location->setDescription($description);

      if ($latitude !== null)
        $outdated_location->setLatitude($latitude);

      if ($longitude !== null)
        $outdated_location->setLongitude($longitude);

      EntityManagerProxy::$entity_manager->flush($outdated_location);

      return $outdated_location;
    }

    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public static function deleteLocation(#[InjectUser] Account $currentAccount, int $id): Location {
      $location = self::location($currentAccount, $id);

      EntityManagerProxy::$entity_manager->remove($location);
      EntityManagerProxy::$entity_manager->flush($location);

      return $location;
    }
  }
}
