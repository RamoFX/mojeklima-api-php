<?php



namespace App\Resources\Limit {

  use App\Resources\Common\Utilities\Debug;
  use App\Resources\Common\Utilities\Headers;
  use App\Resources\Limit\Attributes\RateLimit;
  use App\Resources\Limit\Exceptions\RateLimitExceeded;
  use Psr\SimpleCache\CacheInterface;
  use Psr\SimpleCache\InvalidArgumentException;



  class RateLimitService {
    public function __construct(
      protected CacheInterface $cache
    ) {}



    /**
     * @throws InvalidArgumentException
     * @throws RateLimitExceeded
     */
    public function apply(RateLimit $rateLimit): void {
      $clientIp = Headers::getClientIp();
      $cacheKey = $this->createCacheKey($rateLimit->limit, $rateLimit->interval, $clientIp);
      $requestCount = $this->cache->get($cacheKey, 0);

      //if ($requestCount >= $rateLimit->limit)
      //  throw new RateLimitExceeded();

      $this->cache->set($cacheKey, $requestCount + 1, $rateLimit->interval);

      Debug::set("REDIS $cacheKey", $requestCount + 1);
    }



    protected function createCacheKey(int $limit, int $interval, string $clientIp): string {
      $clientIp = urlencode($clientIp);

      return "rateLimit#$limit#$interval#$clientIp";
    }
  }
}
