<?php



namespace App\Resources\Alert {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Alert\Enums\Criteria;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Common\Exceptions\LimitExceeded;
  use App\Resources\Common\Utilities\GlobalProxy;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  readonly class AlertController {
    private AlertService $alertService;



    /**
     * @throws Exception
     */
    public function __construct() {
      $this->alertService = GlobalProxy::$container->get(AlertService::class);
    }



    /**
     * @return AlertEntity[]
     */
    #[Query]
    #[Logged]
    public function allAlerts(#[InjectUser] AccountEntity $currentAccount): array {
      return $this->alertService->allAlerts($currentAccount);
    }

    #[Query]
    #[Logged]
    public function allAlertsCount(#[InjectUser] AccountEntity $currentAccount): int {
      return $this->alertService->allAlertsCount($currentAccount);
    }

    /**
     * @return AlertEntity[]
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public function locationAlerts(#[InjectUser] AccountEntity $currentAccount, int $locationId): array {
      return $this->alertService->locationAlerts($currentAccount, $locationId);
    }

    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public function locationAlertsCount(#[InjectUser] AccountEntity $current_account, int $locationId): int {
      return $this->alertService->locationAlertsCount($current_account, $locationId);
    }

    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public function alert(#[InjectUser] AccountEntity $currentAccount, int $id): AlertEntity {
      return $this->alertService->alert($currentAccount, $id);
    }

    /**
     * @throws OptimisticLockException
     * @throws GraphQLException
     * @throws ORMException
     * @throws EntityNotFound
     */
    #[Mutation]
    #[Logged]
    public function toggleAlert(#[InjectUser] AccountEntity $currentAccount, int $id, bool $isEnabled): AlertEntity {
      return $this->alertService->toggleAlert($currentAccount, $id, $isEnabled);
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
    public function createAlert(#[InjectUser] AccountEntity $currentAccount, int $locationId, bool $isEnabled, Criteria $criteria, float $rangeFrom, float $rangeTo, int $updateFrequency, string $message): AlertEntity {
      return $this->alertService->createAlert($currentAccount, $locationId, $isEnabled, $criteria, $rangeFrom, $rangeTo, $updateFrequency, $message);
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
    public function updateAlert(#[InjectUser] AccountEntity $currentAccount, int $id, ?bool $isEnabled, ?Criteria $criteria, ?float $rangeFrom, ?float $rangeTo, ?int $updateFrequency, ?string $message): AlertEntity {
      return $this->alertService->updateAlert($currentAccount, $id, $isEnabled, $criteria, $rangeFrom, $rangeTo, $updateFrequency, $message);
    }

    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws ORMException
     */
    #[Mutation]
    #[Logged]
    public function deleteAlert(#[InjectUser] AccountEntity $currentAccount, int $id): AlertEntity {
      return $this->alertService->deleteAlert($currentAccount, $id);
    }
  }
}
