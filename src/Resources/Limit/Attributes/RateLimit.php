<?php



namespace App\Resources\Limit\Attributes {

  use App\Resources\Common\Utilities\Headers;
  use App\Resources\Limit\Exceptions\RateLimitExceeded;
  use Attribute;
  use DI\Container;
  use DI\DependencyException;
  use DI\NotFoundException;
  use Psr\SimpleCache\CacheInterface;
  use Psr\SimpleCache\InvalidArgumentException;
  use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;



  #[Attribute(Attribute::TARGET_METHOD)]
  class RateLimit implements MiddlewareAnnotationInterface {
    public int $limit;
    public int $interval;
    protected CacheInterface $cache;



    /**
     * @param $options array{limit: int, interval: int}
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(array $options) {
      $this->limit = $options['limit'];
      $this->interval = $options['interval'];

      /** @var $container Container */
      $container = require SETUP_PATH . '/container.php';
      $this->cache = $container->get(CacheInterface::class);
    }



    /**
     * @throws InvalidArgumentException
     * @throws RateLimitExceeded
     */
    public function consume(): void {
      $clientIp = Headers::getClientIp();
      $cacheKey = $this->createCacheKey($clientIp);
      $requestCount = $this->cache->get($cacheKey, 0);

      if ($requestCount >= $this->limit)
        throw new RateLimitExceeded();

      $this->cache->set($cacheKey, $requestCount + 1, $this->interval);
    }



    public function createCacheKey(string $clientIp): string {
      return "rateLimit#$this->limit#$this->interval#$clientIp";
    }
  }
}
