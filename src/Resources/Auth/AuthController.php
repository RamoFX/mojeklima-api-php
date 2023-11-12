<?php



namespace App\Resources\Auth {

  use App\Resources\Account\Exceptions\AccountMarkedAsRemoved;
  use App\Resources\Account\Exceptions\EmailNotFound;
  use App\Resources\Account\Exceptions\EmailNotVerified;
  use App\Resources\Auth\Exceptions\IncorrectPassword;
  use App\Resources\Auth\Exceptions\InvalidToken;
  use App\Resources\Auth\Exceptions\TokenExpired;
  use App\Resources\Auth\InputTypes\LoginInput;
  use App\Resources\Auth\InputTypes\TokenOutput;
  use Psr\SimpleCache\InvalidArgumentException;
  use TheCodingMachine\GraphQLite\Annotations\Logged;
  use TheCodingMachine\GraphQLite\Annotations\Mutation;



  readonly class AuthController {
    public function __construct(
      protected AuthService $authService
    ) {}



    /**
     * @throws AccountMarkedAsRemoved
     * @throws EmailNotFound
     * @throws IncorrectPassword
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws EmailNotVerified
     * @throws InvalidArgumentException
     */
    #[Mutation]
    public function login(LoginInput $login): TokenOutput {
      return $this->authService->login($login);
    }



    /**
     * @throws InvalidArgumentException
     */
    #[Mutation]
    #[Logged]
    public function logout(): string {
      return $this->authService->logout();
    }



    /**
     * @throws InvalidToken
     * @throws TokenExpired
     * @throws InvalidArgumentException
     */
    #[Mutation]
    #[Logged]
    public function renewToken(): TokenOutput {
      return $this->authService->renewToken();
    }
  }
}
