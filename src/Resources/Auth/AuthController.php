<?php



namespace App\Resources\Auth {

  use App\Resources\Account\AccountEntity;
  use App\Resources\Account\Exceptions\AccountMarkedAsRemoved;
  use App\Resources\Account\Exceptions\EmailNotFound;
  use App\Resources\Account\InputTypes\CreateAccount;
  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\IncorrectPassword;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use Doctrine\ORM\Exception\ORMException;
  use Doctrine\ORM\NonUniqueResultException;
  use Doctrine\ORM\OptimisticLockException;
  use Exception;
  use TheCodingMachine\GraphQLite\Annotations\InjectUser;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  readonly class AuthController {
    public function __construct(
      protected AuthService $authService
    ) {}



    /**
     * @throws IncorrectPassword
     * @throws EmailNotFound
     * @throws AccountMarkedAsRemoved
     */
    #[Mutation]
    public function login(string $email, string $password, bool $remember): string {
      return $this->authService->login($email, $password, $remember);
    }



    /**
     * @throws GraphQLException
     * @throws ORMException
     * @throws Exception
     */
    #[Mutation]
    public function register(CreateAccount $account): bool {
      return $this->authService->register($account);
    }



    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws NonUniqueResultException
     * @throws EmailNotFound
     * @throws AccountMarkedAsRemoved
     */
    #[Mutation]
    public function resetPassword(string $email): bool {
      return $this->authService->resetPassword($email);
    }



    /**
     * @throws InvalidToken
     * @throws AuthorizationHeaderMissing
     * @throws TokenExpired
     * @throws BearerTokenMissing
     */
    #[Mutation]
    #[Logged]
    public function renewToken(#[InjectUser] AccountEntity $currentAccount, bool $remember): string {
      return $this->authService->renewToken($currentAccount, $remember);
    }
  }
}
