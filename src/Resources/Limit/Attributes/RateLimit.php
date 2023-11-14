<?php



namespace App\Resources\Limit\Attributes {

  use App\Resources\Limit\DTO\RateLimitRule;
  use Attribute;
  use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;



  #[Attribute(Attribute::TARGET_METHOD)]
  class RateLimit implements MiddlewareAnnotationInterface {
    public function __construct(
      public readonly int $limit,
      public readonly int $interval
    ) {}
  }
}
