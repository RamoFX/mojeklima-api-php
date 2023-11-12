<?php



namespace App\Resources\Common {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Auth\AuthService;
  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use DI\Container;
  use DI\DependencyException;
  use DI\NotFoundException;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use Doctrine\ORM\TransactionRequiredException;



  abstract class CommonService {
    protected AccountEntity|null $currentAccount;



    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function __construct() {
      /** @var $container Container */
      $container = require SETUP_PATH . '/container.php';
      $authService = $container->get(AuthService::class);
      $this->currentAccount = $authService->getUser();
    }
  }
}
