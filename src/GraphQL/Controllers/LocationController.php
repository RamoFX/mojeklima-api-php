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
    public static function locations(Account $currentAccount): array {
      return $currentAccount->getLocations();
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function locationsCount(Account $currentAccount): int {
      // SPEED COMPARISON

      // implementation 1 - should be slower (avg 220 ms)
      return count($currentAccount->getLocations());

      // implementation 21 - should be faster (avg yyy ms)
//      $account_id = $currentAccount->getId();
//
//      /** @var $count int */
//      $count = EntityManagerProxy::$entity_manager->createQueryBuilder()
//        ->select("count(location.id)")
//        ->from(Location::class, "location")
//        ->where("location.account_id = :account_id")
//        ->setParameter("account_id", $account_id)
//        ->getQuery()
//        ->getSingleScalarResult();
//
//      return $count;
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function location(Account $currentAccount, int $id): Location {
      $locations = self::locations($currentAccount);

      foreach ($locations as $location) {
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
      $outdatedLocation = self::location($currentAccount, $id);

      if ($name !== null)
        $outdatedLocation->setName($name);

      if ($description !== null)
        $outdatedLocation->setDescription($description);

      if ($latitude !== null)
        $outdatedLocation->setLatitude($latitude);

      if ($longitude !== null)
        $outdatedLocation->setLongitude($longitude);

      EntityManagerProxy::$entity_manager->flush($outdatedLocation);

      return $outdatedLocation;
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
