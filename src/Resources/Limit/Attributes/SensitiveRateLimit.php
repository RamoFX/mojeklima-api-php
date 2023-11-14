<?php



namespace App\Resources\Limit\Attributes {

  use App\Resources\Limit\DTO\RateLimitRule;
  use Attribute;



  #[Attribute(Attribute::TARGET_METHOD)]
  class SensitiveRateLimit extends RateLimit {
    public function __construct() {
      parent::__construct(limit: 4, interval: 60 * 60);
    }
  }
}
