<?php



namespace App\Core\Enums {

  enum AccountRole: string {
    case SYSTEM = "SYSTEM";
    case ADMIN = "ADMIN";
    case USER = "USER";
  }
}
