<?php



namespace App\Resources\Location {

  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Common\CommonService;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use App\Resources\Location\InputTypes\CreateLocationInput;
  use App\Resources\Location\InputTypes\DeleteLocationInput;
  use App\Resources\Location\InputTypes\LocationInput;
  use App\Resources\Location\InputTypes\UpdateLocationInput;
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
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class LocationService extends CommonService {
    protected EntityRepository $repository;



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
      protected EntityManager $entityManager
    ) {
      parent::__construct();
      $this->repository = $entityManager->getRepository(LocationEntity::class);
    }



    /**
     * @param
     * @return LocationEntity[]
     */
    public function locations(): array {
      return $this->currentAccount->getLocations();
    }



    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function locationsCount(): int {
      return $this->repository->createQueryBuilder('l')
        ->select('COUNT(l.id)')
        ->where('l.account = :account')
        ->setParameter('account', $this->currentAccount)
        ->getQuery()
        ->getSingleScalarResult();
    }



    /**
     * @throws EntityNotFound
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function location(LocationInput $location): LocationEntity {
      /** @var $foundLocation LocationEntity */
      $foundLocation = $this->repository->createQueryBuilder('l')
        ->select('l')
        ->join('l.account', 'ac')
        ->where('ac.id = :accountId')
        ->andWhere('l.id = :locationId')
        ->setParameter('accountId', $this->currentAccount->getId())
        ->setParameter('locationId', $location->id)
        ->getQuery()
        ->getSingleResult();

      if (!$foundLocation) {
        throw new EntityNotFound('Location');
      }

      return $foundLocation;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     */
    // TODO: Avoid user from changing the city and country name
    public function createLocation(CreateLocationInput $createLocation): LocationEntity {
      $newLocation = new LocationEntity(
        $createLocation->cityName,
        $createLocation->countryName,
        $createLocation->label,
        $createLocation->latitude,
        $createLocation->longitude
      );

      $this->currentAccount->addLocation($newLocation);
      $this->entityManager->persist($newLocation);
      $this->entityManager->flush($newLocation);

      return $newLocation;
    }



    /**
     * @throws OptimisticLockException
     * @throws EntityNotFound
     * @throws GraphQLException
     * @throws ORMException
     */
    // TODO: Avoid user from changing the city and country name
    public function updateLocation(UpdateLocationInput $updateLocation): LocationEntity {
      /** @var $location LocationEntity */
      $location = $this->repository->createQueryBuilder('l')
        ->select('l')
        ->join('l.account', 'ac')
        ->where('ac.id = :accountId')
        ->andWhere('l.id = :locationId')
        ->setParameter('accountId', $this->currentAccount->getId())
        ->setParameter('locationId', $updateLocation->id)
        ->getQuery()
        ->getSingleResult();

      if ($updateLocation->cityName !== null)
        $location->setCityName($updateLocation->cityName);

      if ($updateLocation->countryName !== null)
        $location->setCountryName($updateLocation->countryName);

      if ($updateLocation->label !== null)
        $location->setLabel($updateLocation->label);

      if ($updateLocation->latitude !== null)
        $location->setLatitude($updateLocation->latitude);

      if ($updateLocation->longitude !== null)
        $location->setLongitude($updateLocation->longitude);

      $this->entityManager->flush($location);

      return $location;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function deleteLocation(DeleteLocationInput $deleteLocation): LocationEntity {
      /** @var $location LocationEntity */
      $location = $this->repository->createQueryBuilder('l')
        ->select('l')
        ->join('l.account', 'ac')
        ->where('ac.id = :accountId')
        ->andWhere('l.id = :locationId')
        ->setParameter('accountId', $this->currentAccount->getId())
        ->setParameter('locationId', $deleteLocation->id)
        ->getQuery()
        ->getSingleResult();

      $this->entityManager->remove($location);
      $this->entityManager->flush($location);

      return $location;
    }
  }
}
