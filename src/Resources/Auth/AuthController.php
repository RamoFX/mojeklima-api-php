<?php



namespace App\Resources\Auth {

  use App\Resources\Account\Exceptions\AccountMarkedAsRemoved;
  use App\Resources\Account\Exceptions\EmailNotFound;
  use App\Resources\Account\Exceptions\EmailNotVerified;
  use App\Resources\Auth\DTO\LoginInput;
  use App\Resources\Auth\Exceptions\AuthorizationHeaderMissing;
  use App\Resources\Auth\Exceptions\BearerTokenMissing;
  use App\Resources\Auth\Exceptions\IncorrectPassword;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\NotAuthenticated;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;



  readonly class AuthController {
    public function __construct(
      protected AuthService $authService
    ) {}



    /**
     * @throws EmailNotFound
     * @throws IncorrectPassword
     * @throws NotAuthenticated
     * @throws AccountMarkedAsRemoved
     * @throws EmailNotVerified
     */
    #[Mutation]
    public function login(LoginInput $login): string {
      return $this->authService->login($login);
    }



    #[Mutation]
    #[Logged]
    public function logout(): bool {
      return $this->authService->logout();
    }



    /**
     * @throws InvalidToken
     * @throws AuthorizationHeaderMissing
     * @throws BearerTokenMissing
     * @throws TokenExpired
     */
    #[Mutation]
    #[Logged]
    public function renewToken(): string {
      return $this->authService->renewToken();
    }
  }
}
