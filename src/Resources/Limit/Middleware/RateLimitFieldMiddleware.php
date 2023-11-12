<?php



namespace App\Resources\Limit\Middleware {

  use App\Resources\Limit\Attributes\RateLimit;
  use App\Resources\Limit\Exceptions\RateLimitExceeded;
  use GraphQL\Type\Definition\FieldDefinition;
  use Psr\SimpleCache\InvalidArgumentException;
  use TheCodingMachine\GraphQLite\Middlewares\FieldHandlerInterface;
  use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
  use TheCodingMachine\GraphQLite\QueryFieldDescriptor;



  class RateLimitFieldMiddleware implements FieldMiddlewareInterface {
    /**
     * @throws InvalidArgumentException
     * @throws RateLimitExceeded
     */
    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): ?FieldDefinition {
      $annotations = $queryFieldDescriptor->getMiddlewareAnnotations();
      /** @var RateLimit $rateLimit */
      $rateLimit = $annotations->getAnnotationByType(RateLimit::class);

      if ($rateLimit === null)
        return $fieldHandler->handle($queryFieldDescriptor);

      $rateLimit->consume();

      return $fieldHandler->handle($queryFieldDescriptor);
    }
  }
}
