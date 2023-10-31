<?php



namespace App\Resources\Auth\Exceptions;

use App\Resources\Common\Utilities\Translation;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



// TODO: Replace existing exception with this and then remove them
class AuthException extends GraphQLException {
  /**
   * @param string|string[] $message
   */
  public function __construct(string|array $message) {
    if (is_array($message)) {
      $message = Translation::translate($message);
    }

    parent::__construct($message, 401);
  }
}
