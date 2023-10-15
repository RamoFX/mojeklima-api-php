<?php



namespace App\GraphQL\Controllers {

  use App\Core\Entities\Account;
  use App\Core\Entities\Alert;
  use App\Core\Entities\Location;
  use App\Core\EntityManagerProxy;
  use App\GraphQL\Exceptions\EntityNotFound;
  use App\GraphQL\Exceptions\LimitExceeded;
  use App\GraphQL\InputTypes\CreateAlertInput;
  use App\GraphQL\InputTypes\UpdateAlertInput;
  use App\GraphQL\Proxies\ContainerProxy;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;



  class AlertController {
    /**
     * @throws EntityNotFound
     */
    private static function getLocation(Account $currentAccount, int $locationId): Location {
      /** @var LocationController $location_controller */
      $location_controller = ContainerProxy::$container->get(LocationController::class);
      $location = $location_controller->location($currentAccount, $locationId);

      return $location;
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$current_account")
     * @return Alert[]
     */
    public static function allAlerts(Account $current_account): array {
      $locations = $current_account->getLocations();
      $alerts = [];

      foreach ($locations as $location) {
        array_push($alerts, ...$location->getAlerts());
      }

      return $alerts;
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function allAlertsCount(Account $current_account): int {
      return count(self::allAlerts($current_account));
    }



    /**
     * @Query()
     * @Logged()
     * @InjectUser(for="$current_account")
     * @return Alert[]
     */
    public static function locationAlerts(Account $current_account, int $locationId): array {
      $location = self::getLocation($current_account, $locationId);

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
     * @InjectUser(for="$current_account")
     */
    public static function alert(Account $current_account, int $id): Alert {
      $alerts = self::allAlerts($current_account);

      foreach ($alerts as $alert) {
        if ($alert->getId() === $id)
          return $alert;
      }

      throw new EntityNotFound("Alert");
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function toggleAlert(Account $current_account, int $id, bool $isEnabled): Alert {
      return self::updateAlert($current_account, $id, $isEnabled, null, null, null, null, null);
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function createAlert(Account $current_account, int $locationId, bool $isEnabled, string $criteria, float $rangeFrom, float $rangeTo, int $updateFrequency, string $message): Alert {
      // check whether user exceeds the limit
      $alerts_count = self::allAlertsCount($current_account);

      if ($alerts_count >= 32)
        throw new LimitExceeded("Alert", 32);



      // create
      $location = self::getLocation($current_account, $locationId);

      $new_alert = new Alert($isEnabled, $criteria, $rangeFrom, $rangeTo, $updateFrequency, $message);

      $location->addAlert($new_alert);

      EntityManagerProxy::$entity_manager->persist($new_alert);
      EntityManagerProxy::$entity_manager->flush($new_alert);

      return $new_alert;
    }



    /**
     * @Mutation()
     * @Logged()
     * @InjectUser(for="$current_account")
     */
    public static function updateAlert(Account $current_account, int $id, ?bool $isEnabled, ?string $criteria, ?float $rangeFrom, ?float $rangeTo, ?int $updateFrequency, ?string $message): Alert {
      $alert = self::alert($current_account, $id);

      if ($isEnabled !== null)
        $alert->setIsEnabled($isEnabled);

      if ($criteria !== null)
        $alert->setCriteria($criteria);

      if ($rangeFrom !== null)
        $alert->setRangeFrom($rangeFrom);

      if ($rangeTo !== null)
        $alert->setRangeTo($rangeTo);

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
     * @InjectUser(for="$current_account")
     */
    public static function deleteAlert(Account $current_account, int $id): Alert {
      $alert = self::alert($current_account, $id);

      EntityManagerProxy::$entity_manager->remove($alert);
      EntityManagerProxy::$entity_manager->flush($alert);

      return $alert;
    }
  }
}
