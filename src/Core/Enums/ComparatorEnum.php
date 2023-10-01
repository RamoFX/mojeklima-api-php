<?php



namespace App\Core\Enums {

  use MyCLabs\Enum\Enum;



  class ComparatorEnum extends Enum {
    private const LESS_THAN = "LESS_THAN";

    private const LESS_THAN_OR_EQUAL_TO = "LESS_THAN_OR_EQUAL_TO";

    private const EQUAL_TO = "EQUAL_TO";

    private const GREATER_THAN_OR_EQUAL_TO = "GREATER_THAN_OR_EQUAL_TO";

    private const GREATER_THAN = "GREATER_THAN";
  }
}
