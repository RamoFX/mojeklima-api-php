<?php



namespace App\Resources\Alert {

  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Alert\DTO\AlertInput;
  use App\Resources\Alert\DTO\CreateAlertInput;
  use App\Resources\Alert\DTO\DeleteAlertInput;
  use App\Resources\Alert\DTO\LocationAlertsCountInput;
  use App\Resources\Alert\DTO\LocationAlertsInput;
  use App\Resources\Alert\DTO\ToggleAlertInput;
  use App\Resources\Alert\DTO\UpdateAlertInput;
  use App\Resources\Alert\Enums\RangeField;
  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Common\CommonService;
  use App\Resources\Common\Enums\ConversionDirection;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Limit\Exceptions\EntityLimitExceeded;
  use App\Resources\Location\DTO\LocationInput;
  use App\Resources\Location\LocationService;
  use DI\DependencyException;
  use DI\NotFoundException;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\EntityRepository;
  use Doctrine\ORM\Exception\NotSupported;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\NoResultException;
  use Doctrine\ORM\OptimisticLockException;
  use Doctrine\ORM\TransactionRequiredException;
  use Exception;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class AlertService extends CommonService {
    protected EntityRepository $repository;
    protected const ALERTS_LIMIT = 32; // TODO: This should be stored in the database



    /**
     * @throws NotSupported
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws DependencyException
     * @throws NotFoundException
     * @throws TransactionRequiredException
     */
    public function __construct(
      protected EntityManager $entityManager,
      protected LocationService $locationService,
      protected AlertConverterService $alertConverter
    ) {
      parent::__construct();
      $this->repository = $entityManager->getRepository(AlertEntity::class);
    }



    /**
     * @return AlertEntity[]
     * @throws Exception
     */
    public function alerts(): array {
      $alerts = $this->repository->createQueryBuilder('al')
        ->select('al')
        ->join('al.location', 'l')
        ->join('l.account', 'ac')
        ->where('ac.id = :accountId')
        ->setParameter('accountId', $this->currentAccount->getId())
        ->getQuery()
        ->getResult();

      return $this->alertConverter->convertMultipleRanges($alerts, ConversionDirection::FROM_METRIC);
    }



    /**
     * @return AlertEntity[]
     */
    public function userEnabledAlerts(): array {
      $alerts = $this->repository->createQueryBuilder('al')
        ->select('al')
        ->join('al.location', 'l')
        ->join('l.account', 'ac')
        ->where('ac.role != :systemRole')
        ->andWhere('al.isEnabled = :true')
        ->setParameter('systemRole', AccountRole::SYSTEM)
        ->setParameter('true', true)
        ->getQuery()
        ->getResult();

      return $this->alertConverter->convertMultipleRanges($alerts, ConversionDirection::FROM_METRIC);
    }



    /**
     * @return AlertEntity[]
     * @throws Exception
     */
    public function enabledAlerts(): array {
      $alerts = $this->repository->createQueryBuilder('a')
        ->select('al')
        ->join('al.location', 'l')
        ->join('l.account', 'ac')
        ->where('al.isEnabled = true')
        ->andWhere('ac.id = :accountId')
        ->setParameter('accountId', $this->currentAccount->getId())
        ->getQuery()
        ->getResult();

      return $this->alertConverter->convertMultipleRanges($alerts, ConversionDirection::FROM_METRIC);
    }



    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function alertsCount(): int {
      return $this->repository->createQueryBuilder('al')
        ->select('COUNT(al.id)')
        ->join('al.location', 'l')
        ->join('l.account', 'ac')
        ->where('ac.id = :accountId')
        ->setParameter('accountId', $this->currentAccount->getId())
        ->getQuery()
        ->getSingleScalarResult();
    }



    /**
     * @return AlertEntity[]
     * @throws Exception
     */
    public function locationAlerts(LocationAlertsInput $locationAlerts): array {
      $alerts = $this->repository->createQueryBuilder('al')
        ->select('al')
        ->join('al.location', 'l')
        ->join('l.account', 'ac')
        ->where('l.id = :locationId')
        ->andWhere('ac.id = :accountId')
        ->setParameter('locationId', $locationAlerts->locationId)
        ->setParameter('accountId', $this->currentAccount->getId())
        ->getQuery()
        ->getResult();

      return $this->alertConverter->convertMultipleRanges($alerts, ConversionDirection::FROM_METRIC);
    }



    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function locationAlertsCount(LocationAlertsCountInput $locationAlertsCount): int {
      return $this->repository->createQueryBuilder('al')
        ->select('COUNT(al.id)')
        ->join('al.location', 'l')
        ->join('l.account', 'ac')
        ->where('l.id = :locationId')
        ->andWhere('ac.id = :accountId')
        ->setParameter('locationId', $locationAlertsCount->locationId)
        ->setParameter('accountId', $this->currentAccount->getId())
        ->getQuery()
        ->getSingleScalarResult();
    }



    /**
     * @throws EntityNotFound
     */
    public function alert(AlertInput $alert): AlertEntity {
      try {
        $alertQueryBuilder = $this->repository->createQueryBuilder('al')
          ->select('al')
          ->where('al.id = :alertId')
          ->setParameter('alertId', $alert->id);

        // do not check whether alert belongs to the system user
        if ($this->currentAccount->getRole() !== AccountRole::SYSTEM) {
          $alertQueryBuilder = $alertQueryBuilder
            ->join('al.location', 'l')
            ->join('l.account', 'ac')
            ->andWhere('ac.id = :accountId')
            ->setParameter('accountId', $this->currentAccount->getId());
        }

        $alert = $alertQueryBuilder->getQuery()->getSingleResult();

        return $this->alertConverter->convertRange($alert, ConversionDirection::FROM_METRIC);
      } catch (Exception) {
        throw new EntityNotFound('Alert');
      }
    }



    /**
     * @throws EntityNotFound
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     * @throws Exception
     */
    public function toggleAlert(ToggleAlertInput $toggleAlert): AlertEntity {
      try {
        /** @var AlertEntity $alert */
        $alert = $this->repository->createQueryBuilder('al')
          ->select('al')
          ->join('al.location', 'l')
          ->join('l.account', 'ac')
          ->where('ac.id = :accountId')
          ->andWhere('al.id = :alertId')
          ->setParameter('accountId', $this->currentAccount->getId())
          ->setParameter('alertId', $toggleAlert->id)
          ->getQuery()
          ->getSingleResult();
      } catch (Exception) {
        throw new EntityNotFound('Alert');
      }

      $alert->setIsEnabled(
        $toggleAlert->isEnabled
      );

      $this->entityManager->persist($alert);
      $this->entityManager->flush($alert);

      return $this->alertConverter->convertRange($alert, ConversionDirection::FROM_METRIC);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     * @throws EntityLimitExceeded
     * @throws Exception
     */
    public function createAlert(CreateAlertInput $createAlert): AlertEntity {
      $alertsCount = (int) $this->repository->createQueryBuilder('al')
        ->select('COUNT(al.id)')
        ->join('al.location', 'l')
        ->join('l.account', 'ac')
        ->where('ac.id = :accountId')
        ->setParameter('accountId', $this->currentAccount->getId())
        ->getQuery()
        ->getSingleScalarResult();

      if ($alertsCount >= self::ALERTS_LIMIT)
        throw new EntityLimitExceeded('Alert', self::ALERTS_LIMIT);

      $location = $this->locationService->location(
        new LocationInput($createAlert->locationId)
      );

      $newAlert = new AlertEntity(
        $createAlert->isEnabled,
        $createAlert->criteria,
        $createAlert->rangeFrom,
        $createAlert->rangeTo,
        $createAlert->updateFrequency,
        $createAlert->message
      );

      $this->alertConverter->convertRange($newAlert, ConversionDirection::TO_METRIC);

      $location->addAlert($newAlert);

      $this->entityManager->persist($newAlert);
      $this->entityManager->flush($newAlert);

      return $this->alertConverter->convertRange($newAlert, ConversionDirection::FROM_METRIC);
    }



    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws ORMException
     * @throws GraphQLException
     * @throws Exception
     */
    public function updateAlert(UpdateAlertInput $updateAlert): AlertEntity {
      try {
        /** @var AlertEntity $alert */
        $alert = $this->repository->createQueryBuilder('al')
          ->select('al')
          ->join('al.location', 'l')
          ->join('l.account', 'ac')
          ->where('ac.id = :accountId')
          ->andWhere('al.id = :alertId')
          ->setParameter('accountId', $this->currentAccount->getId())
          ->setParameter('alertId', $updateAlert->id)
          ->getQuery()
          ->getSingleResult();
      } catch (NoResultException) {
        throw new EntityNotFound('Alert');
      }

      $criteria = $updateAlert->criteria ?? $alert->getCriteria();

      if ($updateAlert->isEnabled !== null)
        $alert->setIsEnabled($updateAlert->isEnabled);

      if ($criteria !== null)
        $alert->setCriteria($criteria);

      if ($updateAlert->rangeFrom !== null) {
        $alert->setRangeFrom($updateAlert->rangeFrom);
        $this->alertConverter->convertRangeField($alert, ConversionDirection::TO_METRIC, RangeField::RANGE_FROM);
      }

      if ($updateAlert->rangeTo !== null) {
        $alert->setRangeTo($updateAlert->rangeTo);
        $this->alertConverter->convertRangeField($alert, ConversionDirection::TO_METRIC, RangeField::RANGE_TO);
      }

      if ($updateAlert->updateFrequency !== null)
        $alert->setUpdateFrequency($updateAlert->updateFrequency);

      if ($updateAlert->message !== null)
        $alert->setMessage($updateAlert->message);

      $this->entityManager->flush($alert);

      return $this->alertConverter->convertRange($alert, ConversionDirection::FROM_METRIC);
    }



    /**
     * @throws EntityNotFound
     * @throws NonUniqueResultException
     * @throws ORMException
     * @throws Exception
     */
    public function deleteAlert(DeleteAlertInput $deleteAlert): AlertEntity {
      try {
        /** @var AlertEntity $alert */
        $alert = $this->repository->createQueryBuilder('al')
          ->select('al')
          ->join('al.location', 'l')
          ->join('l.account', 'ac')
          ->where('ac.id = :accountId')
          ->setParameter('accountId', $this->currentAccount->getId())
          ->where('al.id = :alertId')
          ->setParameter('alertId', $deleteAlert->id)
          ->getQuery()
          ->getSingleResult();
      } catch (NoResultException) {
        throw new EntityNotFound('Alert');
      }

      $this->entityManager->remove($alert);
      $this->entityManager->flush($alert);

      return $this->alertConverter->convertRange($alert, ConversionDirection::FROM_METRIC);
    }
  }
}
