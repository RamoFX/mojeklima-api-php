<?php



namespace App\Resources\Common\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class LimitExceeded extends GraphQLException {
    /**
     * @param string $entityName
     * @param float|int $limit
     */
    public function __construct(string $entityName, $limit) {
      $message = Translation::translate([
        "cs" => "Limit vyčerpán, entita: \"$entityName\", limit: $limit",
        "en" => "Limit exceeded, entity: \"$entityName\", limit: $limit",
        "de" => "Grenzwert überschritten, Entität: \"$entityName\", Grenzwert: $limit",
      ]);

      parent::__construct($message, 404);
    }
  }
}
