<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Alert;
  use App\Core\Entities\Location;
  use App\Core\EntityManagerProxy;
  use App\Core\Enums\Criteria;
  use App\GraphQL\Exceptions\EntityNotFound;
  use App\GraphQL\Exceptions\LimitExceeded;
  use App\GraphQL\Proxies\ContainerProxy;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class AlertController {
    /**
     * @throws EntityNotFound
     */
    private static function getLocation(Account $currentAccount, int $locationId): Location {
      $location_controller = ContainerProxy::$container->get(LocationController::class);

      return $location_controller->location($currentAccount, $locationId);
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     * @return Alert[]
     */
    public static function allAlerts(Account $currentAccount): array {
      $locations = $currentAccount->getLocations();
      $alerts = [];

      foreach ($locations as $location) {
        array_push($alerts, ...$location->getAlerts());
      }

      return $alerts;
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function allAlertsCount(Account $currentAccount): int {
      return count(self::allAlerts($currentAccount));
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     * @return Alert[]
     */
    public static function locationAlerts(Account $currentAccount, int $locationId): array {
      $location = self::getLocation($currentAccount, $locationId);

      return $location->getAlerts();
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function locationAlertsCount(Account $current_account, int $locationId): int {
      return count(self::locationAlerts($current_account, $locationId));
    }


    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function alert(Account $currentAccount, int $id): Alert {
      $alerts = self::allAlerts($currentAccount);

      foreach ($alerts as $alert) {
        if ($alert->getId() === $id)
          return $alert;
      }

      throw new EntityNotFound("Alert");
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function toggleAlert(Account $currentAccount, int $id, bool $isEnabled): Alert {
      return self::updateAlert($currentAccount, $id, $isEnabled, null, null, null, null, null, null);
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function createAlert(Account $currentAccount, int $locationId, bool $isEnabled, string $criteria, float $rangeFrom, float $rangeTo, int $updateFrequency, string $message, WeatherUnitsEnum $units): Alert {
      // check whether user exceeds the limit
      $alerts_count = self::allAlertsCount($currentAccount);

      if ($alerts_count >= 32)
        throw new LimitExceeded("Alert", 32);

      // create
      $location = self::getLocation($currentAccount, $locationId);

      $new_alert = new Alert($isEnabled, $criteria, $rangeFrom, $rangeTo, $updateFrequency, $message);
      $new_alert->convertRange($criteria, $units);

      $location->addAlert($new_alert);

      EntityManagerProxy::$entity_manager->persist($new_alert);
      EntityManagerProxy::$entity_manager->flush($new_alert);

      return $new_alert;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function updateAlert(Account $currentAccount, int $id, ?bool $isEnabled, ?string $criteria, ?float $rangeFrom, ?float $rangeTo, ?int $updateFrequency, ?string $message, ?WeatherUnitsEnum $units): Alert {
      $alert = self::alert($currentAccount, $id);
      $criteria = $criteria ?? $alert->getCriteria();

      // validate units
      if (($rangeFrom !== null || $rangeTo !== null) && $units === null) {
        throw new GraphQLException("If you are specifying range values, then you have to provide units.");
      }

      // update
      if ($isEnabled !== null)
        $alert->setIsEnabled($isEnabled);

      if ($criteria !== null)
        $alert->setCriteria($criteria);

      if ($rangeFrom !== null) {
        $alert->setRangeFrom($rangeFrom);
        $alert->convertRangeFrom($criteria, $units);
      }

      if ($rangeTo !== null) {
        $alert->setRangeTo($rangeTo);
        $alert->convertRangeTo($criteria, $units);
      }

      if ($updateFrequency !== null)
        $alert->setUpdateFrequency($updateFrequency);

      if ($message !== null)
        $alert->setMessage($message);

      EntityManagerProxy::$entity_manager->flush($alert);

      return $alert;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$currentAccount")
     */
    public static function deleteAlert(Account $currentAccount, int $id): Alert {
      $alert = self::alert($currentAccount, $id);

      EntityManagerProxy::$entity_manager->remove($alert);
      EntityManagerProxy::$entity_manager->flush($alert);

      return $alert;
    }
  }
}
