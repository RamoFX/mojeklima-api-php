<?php



namespace App\Resources\Limit {

  use App\Resources\Common\Utilities\ConfigManager;
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
      $timestamp = time();
      $timestamps = $this->cache->get($cacheKey, []);

      // remove older than interval
      $timestamps = array_filter($timestamps, function($time) use ($interval) {
        return $time >= (time() - $interval);
      });

      if (count($timestamps) >= $limit) {
        throw new RateLimitExceeded();
      }

      // include current request timestamp
      $timestamps[] = $timestamp;
      $this->cache->set($cacheKey, $timestamps, $interval);
    }



    protected function createCacheKey(string $clientIp): string {
      $clientIp = urlencode($clientIp);

      return "rateLimit#$clientIp";
    }
  }
}
