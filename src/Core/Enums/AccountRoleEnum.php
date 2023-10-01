<?php



namespace App\Core\Enums {

  use MyCLabs\Enum\Enum;



  class AccountRoleEnum extends Enum {
    private const SYSTEM = "SYSTEM";

    private const ADMIN = "ADMIN";

    private const USER = "USER";
  }
}
