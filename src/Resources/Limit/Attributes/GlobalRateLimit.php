<?php



namespace App\Resources\Limit\Attributes {

  use App\Resources\Limit\DTO\RateLimitRule;
  use Attribute;



  #[Attribute(Attribute::TARGET_METHOD)]
  class GlobalRateLimit extends RateLimit {
    public function __construct() {
      parent::__construct(limit: 60, interval: 60);
    }
  }
}
