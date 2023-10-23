<?php



namespace App\Resources\Account\Enums {

  enum AccountRole: string {
    case SYSTEM = "SYSTEM";
    case ADMIN = "ADMIN";
    case USER = "USER";
  }
}
