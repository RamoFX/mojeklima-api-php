<?php



namespace App\Resources\Limit {

  use App\Resources\Common\Utilities\ConfigManager;
  use App\Resources\Common\Utilities\Debug;
  use App\Resources\Common\Utilities\Headers;
  use App\Resources\Limit\Exceptions\RateLimitExceeded;
  use Psr\SimpleCache\CacheInterface;
  use Psr\SimpleCache\InvalidArgumentException;



  class RateLimitService {
    public function __construct(
      protected CacheInterface $cache,
      protected ConfigManager $config
    ) {}



    /**
     * @throws InvalidArgumentException
     * @throws RateLimitExceeded
     */
    public function apply(): void {
      $limit = $this->config->get('rateLimit.limit');
      $interval = $this->config->get('rateLimit.interval');
      $clientIp = Headers::getClientIp();
      $cacheKey = $this->createCacheKey($clientIp);
      $requestCount = $this->cache->get($cacheKey, 0);

      if ($requestCount >= $limit)
        throw new RateLimitExceeded();

      $this->cache->set($cacheKey, $requestCount + 1, $interval);

      Debug::set("REDIS $cacheKey", $requestCount + 1);
    }



    protected function createCacheKey(string $clientIp): string {
      $clientIp = urlencode($clientIp);

      return "rateLimit#$clientIp";
    }
  }
}
