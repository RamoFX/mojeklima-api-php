<?php



namespace App\Resources\Auth {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Account\Enums\AccountRole;
  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Auth\Utilities\JWT;
  use App\Resources\Common\Utilities\GlobalProxy;
  use App\Resources\Common\Utilities\Headers;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\OptimisticLockException;
  use Doctrine\ORM\TransactionRequiredException;
  use Exception;
  use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
  use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;



  class AuthService implements AuthenticationServiceInterface, AuthorizationServiceInterface {
    /**
     * @throws BearerTokenMissing
     * @throws AuthorizationHeaderMissing
     */
    public static function getBearerToken(): string {
      $authorization = Headers::get("Authorization");

      if ($authorization === null)
        throw new AuthorizationHeaderMissing();

      $isBearer = str_starts_with($authorization, "Bearer");

      if (!$isBearer)
        throw new BearerTokenMissing();

      return substr($authorization, strlen("Bearer "));
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
    public function getUser(): ?AccountEntity {
      $headerToken = self::getBearerToken();
      $tokenDecoded = JWT::decodeToken($headerToken);

      return GlobalProxy::$entityManager->find(AccountEntity::class, $tokenDecoded["id"]);
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

    private static function isAdminAccount(AccountEntity $account): bool {
      return $account->getRole() === AccountRole::ADMIN;
    }

    private static function isSystemAccount(AccountEntity $account): bool {
      return $account->getRole() === AccountRole::SYSTEM;
    }
  }
}
