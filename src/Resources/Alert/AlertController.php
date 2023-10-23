<?php



namespace App\Resources\Alert {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Alert\Enums\Criteria;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Common\Exceptions\LimitExceeded;
  use App\Resources\Common\Utilities\GlobalProxy;
  use App\Resources\Location\LocationController;
  use App\Resources\Location\LocationEntity;
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
     * @throws EntityNotFound|Exception
     */
    private static function getLocation(AccountEntity $currentAccount, int $locationId): LocationEntity {
      $location_controller = GlobalProxy::$container->get(LocationController::class); // TODO: Use injected locationService

      return $location_controller->location($currentAccount, $locationId);
    }

    /**
     * @return AlertEntity[]
     */
    #[Query]
    #[Logged]
    public static function allAlerts(#[InjectUser] AccountEntity $currentAccount): array {
      $locations = $currentAccount->getLocations();
      $alerts = [];

      foreach ($locations as $location) {
        array_push($alerts, ...$location->getAlerts());
      }

      return $alerts;
    }

    #[Query]
    #[Logged]
    public static function allAlertsCount(#[InjectUser] AccountEntity $currentAccount): int {
      return count(self::allAlerts($currentAccount));
    }

    /**
     * @return AlertEntity[]
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public static function locationAlerts(#[InjectUser] AccountEntity $currentAccount, int $locationId): array {
      $location = self::getLocation($currentAccount, $locationId);

      return $location->getAlerts();
    }

    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public static function locationAlertsCount(#[InjectUser] AccountEntity $current_account, int $locationId): int {
      return count(self::locationAlerts($current_account, $locationId));
    }

    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public static function alert(#[InjectUser] AccountEntity $currentAccount, int $id): AlertEntity {
      $alerts = self::allAlerts($currentAccount);

      foreach ($alerts as $alert) {
        if ($alert->getId() === $id) {
          return $alert;
        }
      }

      throw new EntityNotFound("Alert");
    }

    /**
     * @throws OptimisticLockException
     * @throws GraphQLException
     * @throws ORMException
     * @throws EntityNotFound
     */
    #[Mutation]
    #[Logged]
    public static function toggleAlert(#[InjectUser] AccountEntity $currentAccount, int $id, bool $isEnabled): AlertEntity {
      return self::updateAlert($currentAccount, $id, $isEnabled, null, null, null, null, null);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     * @throws EntityNotFound
     * @throws LimitExceeded
     * @throws Exception
     */
    #[Mutation]
    #[Logged]
    public static function createAlert(#[InjectUser] AccountEntity $currentAccount, int $locationId, bool $isEnabled, Criteria $criteria, float $rangeFrom, float $rangeTo, int $updateFrequency, string $message): AlertEntity {
      // check whether user exceeds the limit
      $alerts_count = self::allAlertsCount($currentAccount);

      if ($alerts_count >= 32)
        throw new LimitExceeded("Alert", 32);

      $location = self::getLocation($currentAccount, $locationId);
      $new_alert = new AlertEntity($isEnabled, $criteria, $rangeFrom, $rangeTo, $updateFrequency, $message);
      //    $new_alert->convertRange($criteria, $units);
      $location->addAlert($new_alert);

      GlobalProxy::$entityManager->persist($new_alert);
      GlobalProxy::$entityManager->flush($new_alert);

      return $new_alert;
    }

    /**
     * @throws OptimisticLockException
     * @throws GraphQLException
     * @throws ORMException
     * @throws EntityNotFound
     * @throws Exception
     */
    #[Mutation]
    #[Logged]
    public static function updateAlert(#[InjectUser] AccountEntity $currentAccount, int $id, ?bool $isEnabled, ?Criteria $criteria, ?float $rangeFrom, ?float $rangeTo, ?int $updateFrequency, ?string $message): AlertEntity {
      $alert = self::alert($currentAccount, $id);
      $criteria = $criteria ?? $alert->getCriteria();

      //    if (($rangeFrom !== null || $rangeTo !== null) && $units === null) {
      //      throw new GraphQLException("If you are specifying range values, then you have to provide units.");
      //    }

      if ($isEnabled !== null)
        $alert->setIsEnabled($isEnabled);

      if ($criteria !== null)
        $alert->setCriteria($criteria);

      if ($rangeFrom !== null) {
        $alert->setRangeFrom($rangeFrom);
        //      $alert->convertRangeFrom($criteria, $units);
      }

      if ($rangeTo !== null) {
        $alert->setRangeTo($rangeTo);
        //      $alert->convertRangeTo($criteria, $units);
      }

      if ($updateFrequency !== null)
        $alert->setUpdateFrequency($updateFrequency);

      if ($message !== null)
        $alert->setMessage($message);

      GlobalProxy::$entityManager->flush($alert);

      return $alert;
    }

    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public static function deleteAlert(#[InjectUser] AccountEntity $currentAccount, int $id): AlertEntity {
      $alert = self::alert($currentAccount, $id);

      GlobalProxy::$entityManager->remove($alert);
      GlobalProxy::$entityManager->flush($alert);

      return $alert;
    }
  }
}
