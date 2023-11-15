<?php



namespace App\Resources\Permission\Enums {

  enum Permission: string {
    const ONLY_TRUSTED = 'ONLY_TRUSTED';

    const ACCOUNT_MANAGEMENT = 'ACCOUNT_MANAGEMENT';
  }
}
