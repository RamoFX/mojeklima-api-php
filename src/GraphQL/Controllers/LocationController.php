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
     * @InjectUser(for="$current_account")
     * @return Location[]
     */
    public static function locations(Account $current_account): array {
      return $current_account->getLocations();
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function locationsCount(Account $current_account): int {
      // SPEED COMPARISON

      // implementation 1 - should be slower (avg 220 ms)
      return count($current_account->getLocations());

      // implementation 21 - should be faster (avg yyy ms)
//      $account_id = $current_account->getId();
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
     * @InjectUser(for="$current_account")
     */
    public static function location(Account $current_account, int $id): Location {
      $locations = self::locations($current_account);

      foreach ($locations as $location) {
        if ($location->getId() === $id)
          return $location;
      }

      throw new EntityNotFound("Location");
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function createLocation(Account $current_account, string $name, string $description, float $latitude, float $longitude): Location {
      $new_location = new Location($name, $description, $latitude, $longitude);

      $current_account->addLocation($new_location);

      EntityManagerProxy::$entity_manager->persist($new_location);
      EntityManagerProxy::$entity_manager->flush($new_location);

      return $new_location;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function updateLocation(Account $current_account, int $id, ?string $name, ?string $description, ?float $latitude, ?float $longitude): Location {
      $outdated_location = self::location($current_account, $id);

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
     * @InjectUser(for="$current_account")
     */
    public static function deleteLocation(Account $current_account, int $id): Location {
      $location = self::location($current_account, $id);

      EntityManagerProxy::$entity_manager->remove($location);
      EntityManagerProxy::$entity_manager->flush($location);

      return $location;
    }
  }
}
