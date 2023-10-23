<?php



namespace App\Resources\Alert {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Alert\Enums\Criteria;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Common\Exceptions\LimitExceeded;
  use App\Resources\Common\Utilities\GlobalProxy;
  use App\Resources\Location\LocationService;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use Exception;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class AlertService {
    private LocationService $locationService;



    /**
     * @throws Exception
     */
    public function __construct() {
      $this->locationService = GlobalProxy::$container->get(LocationService::class);
    }



    /**
     * @param AccountEntity $currentAccount
     * @return AlertEntity[]
     */
    public function allAlerts(AccountEntity $currentAccount): array {
      $locations = $currentAccount->getLocations();
      $alerts = [];

      foreach ($locations as $location) {
        array_push($alerts, ...$location->getAlerts());
      }

      return $alerts;
    }



    public function allAlertsCount(AccountEntity $currentAccount): int {
      return count($this->allAlerts($currentAccount));
    }



    /**
     * @throws EntityNotFound
     */
    public function locationAlerts(AccountEntity $currentAccount, int $locationId) {
      $location = $this->locationService->location($currentAccount, $locationId);

      return $location->getAlerts();
    }



    /**
     * @throws EntityNotFound
     */
    public function locationAlertsCount(AccountEntity $current_account, int $locationId): int {
      return count($this->locationAlerts($current_account, $locationId));
    }



    /**
     * @throws EntityNotFound
     */
    public function alert(AccountEntity $currentAccount, int $id): AlertEntity {
      $alerts = $this->allAlerts($currentAccount);

      foreach ($alerts as $alert) {
        if ($alert->getId() === $id) {
          return $alert;
        }
      }

      throw new EntityNotFound("Alert");
    }



    /**
     * @throws EntityNotFound
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     */
    public function toggleAlert(AccountEntity $currentAccount, int $id, bool $isEnabled): AlertEntity {
      return $this->updateAlert($currentAccount, $id, $isEnabled, null, null, null, null, null);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     * @throws LimitExceeded
     */
    public function createAlert(AccountEntity $currentAccount, int $locationId, bool $isEnabled, Criteria $criteria, float $rangeFrom, float $rangeTo, int $updateFrequency, string $message): AlertEntity {
      // check whether user exceeds the limit
      $alerts_count = $this->allAlertsCount($currentAccount);

      if ($alerts_count >= 32)
        throw new LimitExceeded("Alert", 32);

      $location = $this->locationService->location($currentAccount, $locationId);
      $new_alert = new AlertEntity($isEnabled, $criteria, $rangeFrom, $rangeTo, $updateFrequency, $message);
      //    $new_alert->convertRange($criteria, $units);
      $location->addAlert($new_alert);

      GlobalProxy::$entityManager->persist($new_alert);
      GlobalProxy::$entityManager->flush($new_alert);

      return $new_alert;
    }



    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws ORMException
     * @throws GraphQLException
     */
    public function updateAlert(AccountEntity $currentAccount, int $id, ?bool $isEnabled, ?Criteria $criteria, ?float $rangeFrom, ?float $rangeTo, ?int $updateFrequency, ?string $message): AlertEntity {
      $alert = $this->alert($currentAccount, $id);
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
    public function deleteAlert(AccountEntity $currentAccount, int $id): AlertEntity {
      $alert = $this->alert($currentAccount, $id);

      GlobalProxy::$entityManager->remove($alert);
      GlobalProxy::$entityManager->flush($alert);

      return $alert;
    }
  }
}
