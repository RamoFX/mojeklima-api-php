<?php



namespace App\Resources\Limit\Exceptions {

  use App\Resources\Common\Utilities\Translation;
  use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;



  class RateLimitExceeded extends GraphQLException {
    /**
     * @param string $entityName
     * @param float|int $limit
     */
    public function __construct() {
      $message = Translation::translate([
        'cs' => 'Byl překročen limit počtu požadavků',
        'en' => 'Request count limit exceeded',
        'de' => 'Grenzwert für die Anzahl der Anforderungen überschritten',
      ]);

      parent::__construct($message, 429);
    }
  }
}
