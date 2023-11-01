<?php



namespace App\Resources\Location {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Common\Exceptions\EntityNotFound;
  use Doctrine\ORM\EntityManager;
  use Doctrine\ORM\EntityRepository;
  use Doctrine\ORM\Exception\NotSupported;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\NoResultException;
  use Doctrine\ORM\OptimisticLockException;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class LocationService {
    protected EntityRepository $repository;



    /**
     * @throws NotSupported
     */
    public function __construct(
      protected EntityManager $entityManager
    ) {
      $this->repository = $entityManager->getRepository(LocationEntity::class);
    }



    /**
     * @param AccountEntity $currentAccount
     * @return LocationEntity[]
     */
    public function locations(AccountEntity $currentAccount): array {
      return $currentAccount->getLocations();
    }



    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function locationsCount(AccountEntity $currentAccount): int {
      return $this->repository->createQueryBuilder('l')
        ->select('COUNT(l.id)')
        ->where('l.account = :account')
        ->setParameter('account', $currentAccount)
        ->getQuery()
        ->getSingleScalarResult();
    }



    /**
     * @throws EntityNotFound
     */
    public function location(AccountEntity $currentAccount, int $id): LocationEntity {
      $location = $this->repository->findOneBy(['account' => $currentAccount, 'id' => $id]);

      if (!$location) {
        throw new EntityNotFound("Location");
      }

      return $location;
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws GraphQLException
     */
    // TODO: Avoid user from changing the city and country name
    public function createLocation(AccountEntity $currentAccount, string $cityName, string $countryName, ?string $label, float $latitude, float $longitude): LocationEntity {
      $newLocation = new LocationEntity($cityName, $countryName, $label, $latitude, $longitude);
      $currentAccount->addLocation($newLocation);

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
    public function updateLocation(AccountEntity $currentAccount, int $id, ?string $cityName, ?string $countryName, ?string $label, ?float $latitude, ?float $longitude): LocationEntity {
      $outdatedLocation = self::location($currentAccount, $id);

      if ($cityName !== null)
        $outdatedLocation->setCityName($cityName);

      if ($countryName !== null)
        $outdatedLocation->setCountryName($countryName);

      if ($label !== null)
        $outdatedLocation->setLabel($label);

      if ($latitude !== null)
        $outdatedLocation->setLatitude($latitude);

      if ($longitude !== null)
        $outdatedLocation->setLongitude($longitude);

      $this->entityManager->flush($outdatedLocation);

      return $outdatedLocation;
    }



    /**
     * @throws EntityNotFound
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function deleteLocation(AccountEntity $currentAccount, int $id): LocationEntity {
      $location = self::location($currentAccount, $id);

      $this->entityManager->remove($location);
      $this->entityManager->flush($location);

      return $location;
    }
  }
}
