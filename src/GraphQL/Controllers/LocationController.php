<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Location;
  use App\Core\EntityManagerProxy;
  use App\GraphQL\Exceptions\EntityNotFound;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  class LocationController {
    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     * @return Location[]
     */
    public static function allLocations(Account $currentAccount): array {
      return $currentAccount->getLocations();
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function locationsCount(Account $currentAccount): int {
      return count(self::allLocations($currentAccount));
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function location(Account $currentAccount, int $id): Location {
      $allLocations = self::allLocations($currentAccount);

      foreach ($allLocations as $location) {
        if ($location->getId() === $id)
          return $location;
      }

      throw new EntityNotFound("Location");
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function createLocation(Account $currentAccount, string $name, string $description, float $latitude, float $longitude): Location {
      $new_location = new Location($name, $description, $latitude, $longitude);

      $currentAccount->addLocation($new_location);

      EntityManagerProxy::$entity_manager->persist($new_location);
      EntityManagerProxy::$entity_manager->flush($new_location);

      return $new_location;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function updateLocation(Account $currentAccount, int $id, ?string $name, ?string $description, ?float $latitude, ?float $longitude): Location {
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
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function deleteLocation(Account $currentAccount, int $id): Location {
      $location = self::location($currentAccount, $id);

      EntityManagerProxy::$entity_manager->remove($location);
      EntityManagerProxy::$entity_manager->flush($location);

      return $location;
    }
  }
}
