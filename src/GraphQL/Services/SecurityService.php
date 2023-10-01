<?php



namespace App\GraphQL\Services {

  use App\Core\Entities\Account;
  use App\Core\EntityManagerProxy;
  use App\Core\Enums\AccountRoleEnum;
  use App\GraphQL\Exceptions\AuthorizationHeaderMissing;
  use App\GraphQL\Exceptions\BearerTokenMissing;
  use App\GraphQL\Exceptions\InvalidToken;
  use App\GraphQL\Exceptions\TokenExpired;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use Doctrine\ORM\TransactionRequiredException;
  use Exception;
  use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
  use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;



  class SecurityService implements AuthenticationServiceInterface, AuthorizationServiceInterface {
    /**
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws InvalidToken
     * @throws TokenExpired
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function getUser(): ?Account {
      $headerToken = HeadersService::getBearerToken();
      $tokenDecoded = JWTService::decodeToken($headerToken);
      $account = EntityManagerProxy::$entity_manager->find(Account::class, $tokenDecoded["id"]);

      return $account;
    }



    public function isLogged(): bool {
      try {
        $isAuthenticated = $this->getUser() !== null;

        return $isAuthenticated;
      } catch (Exception $exception) {
        return false;
      }
    }



    /**
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws InvalidToken
     * @throws TokenExpired
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function isAllowed(string $right, $subject = null): bool {
      $account = $this->getUser();

      if ($account === null)
        return false;

      switch ($right) {
        case "CAN_CHANGE_ROLE":
          return self::isAdminAccount($account);

        case "CAN_ACCESS_USERS":
          return self::isSystemAccount($account);

        case "CAN_SEND_PUSH_NOTIFICATIONS":
          return self::isAdminAccount($account) || self::isSystemAccount($account);

        default:
          return false;
      }
    }



    private static function isAdminAccount(Account $account): bool {
      return $account->getRole() === 'ADMIN';
    }

    private static function isSystemAccount(Account $account): bool {
      return $account->getRole() === 'SYSTEM';
    }
  }
}
