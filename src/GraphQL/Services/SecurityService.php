<?php



namespace App\GraphQL\Services {

  use App\Core\Entities\Account;
  use App\Core\EntityManagerProxy;
  use App\Core\Enums\AccountRole;
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

      return EntityManagerProxy::$entity_manager->find(Account::class, $tokenDecoded["id"]);
    }

    public function isLogged(): bool {
      try {
        return $this->getUser() !== null;
      } catch (Exception) {
        return false;
      }
    }

    /**
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     */
    public function isAllowed(string $right, $subject = null): bool {
      $account = $this->getUser();

      if ($account === null)
        return false;

      return match ($right) {
        "ACCOUNT_MANAGEMENT" => self::isAdminAccount($account) || self::isSystemAccount($account),
        default => false,
      };
    }

    private static function isAdminAccount(Account $account): bool {
      return $account->getRole() === AccountRole::ADMIN;
    }

    private static function isSystemAccount(Account $account): bool {
      return $account->getRole() === AccountRole::SYSTEM;
    }
  }
}
