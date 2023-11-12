<?php



namespace App\Resources\Alert {

  use App\Resources\Alert\InputTypes\AlertInput;
  use App\Resources\Alert\InputTypes\CreateAlertInput;
  use App\Resources\Alert\InputTypes\DeleteAlertInput;
  use App\Resources\Alert\InputTypes\LocationAlertsCountInput;
  use App\Resources\Alert\InputTypes\LocationAlertsInput;
  use App\Resources\Alert\InputTypes\ToggleAlertInput;
  use App\Resources\Alert\InputTypes\UpdateAlertInput;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Limit\Exceptions\EntityLimitExceeded;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\NoResultException;
  use Doctrine\ORM\OptimisticLockException;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Annotations\Query;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  readonly class AlertController {
    public function __construct(
      protected AlertService $alertService
    ) {}



    /**
     * @return AlertEntity[]
     * @throws Exception
     */
    #[Query]
    #[Logged]
    public function alerts(): array {
      return $this->alertService->alerts();
    }



    /**
     * @return AlertEntity[]
     * @throws Exception
     */
    #[Query]
    #[Logged]
    public function enabledAlerts(): array {
      return $this->alertService->enabledAlerts();
    }



    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Query]
    #[Logged]
    public function alertsCount(): int {
      return $this->alertService->alertsCount();
    }



    /**
     * @param LocationAlertsInput $locationAlerts
     * @return AlertEntity[]
     * @throws Exception
     */
    #[Query]
    #[Logged]
    public function locationAlerts(LocationAlertsInput $locationAlerts): array {
      return $this->alertService->locationAlerts($locationAlerts);
    }



    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    #[Query]
    #[Logged]
    public function locationAlertsCount(LocationAlertsCountInput $locationAlertsCount): int {
      return $this->alertService->locationAlertsCount($locationAlertsCount);
    }



    /**
     * @throws EntityNotFound
     */
    #[Query]
    #[Logged]
    public function alert(AlertInput $alert): AlertEntity {
      return $this->alertService->alert($alert);
    }



    /**
     * @throws OptimisticLockException
     * @throws GraphQLException
     * @throws ORMException
     * @throws EntityNotFound
     */
    #[Mutation]
    #[Logged]
    public function toggleAlert(ToggleAlertInput $toggleAlert): AlertEntity {
      return $this->alertService->toggleAlert($toggleAlert);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     * @throws EntityNotFound
     * @throws EntityLimitExceeded
     * @throws Exception
     */
    #[Mutation]
    #[Logged]
    public function createAlert(CreateAlertInput $createAlert): AlertEntity {
      return $this->alertService->createAlert($createAlert);
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
    public function updateAlert(UpdateAlertInput $updateAlert): AlertEntity {
      return $this->alertService->updateAlert($updateAlert);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws EntityNotFound
     */
    #[Mutation]
    #[Logged]
    public function deleteAlert(DeleteAlertInput $deleteAlert): AlertEntity {
      return $this->alertService->deleteAlert($deleteAlert);
    }
  }
}
