<?php



namespace App\Resources\Limit\Middleware {

  use App\Resources\Common\Utilities\Debug;
  use App\Resources\Limit\Attributes\GlobalRateLimit;
  use App\Resources\Limit\Attributes\RateLimit;
  use App\Resources\Limit\Exceptions\RateLimitExceeded;
  use App\Resources\Limit\RateLimitService;
  use GraphQL\Type\Definition\FieldDefinition;
  use Psr\SimpleCache\InvalidArgumentException;
  use TheCodingMachine\GraphQLite\Middlewares\FieldHandlerInterface;
  use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
  use TheCodingMachine\GraphQLite\QueryFieldDescriptor;



  class RateLimitFieldMiddleware implements FieldMiddlewareInterface {
    public function __construct(
      protected RateLimitService $rateLimitService
    ) {}



    /**
     * @throws InvalidArgumentException
     * @throws RateLimitExceeded
     */
    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): ?FieldDefinition {
      $annotations = $queryFieldDescriptor->getMiddlewareAnnotations();
      /** @var RateLimit $rateLimit */
      $rateLimit = $annotations->getAnnotationByType(RateLimit::class);
      $rateLimit ??= new GlobalRateLimit();

      // TODO FIX: Called for every mutation and query!
      $k = 'DEBUG middleware';
      $m = Debug::get($k, []);
      $m[] = $queryFieldDescriptor->getName();
      Debug::set($k, $m);

      $this->rateLimitService->apply($rateLimit);

      return $fieldHandler->handle($queryFieldDescriptor);
    }
  }
}
