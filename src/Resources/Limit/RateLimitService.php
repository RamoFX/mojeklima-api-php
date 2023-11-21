<?php



namespace App\Resources\Limit {

  use App\Resources\Common\Utilities\ConfigManager;
  use App\Resources\Common\Utilities\Headers;
  use App\Resources\Limit\Exceptions\RateLimitExceeded;
  use Psr\SimpleCache\CacheInterface;
  use Psr\SimpleCache\InvalidArgumentException;
  use Throwable;



  class RateLimitService {
    private const CACHE_KEY_NAMESPACE = 'rateLimit';



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
      $cacheKey = $this->createCacheKey();
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



    protected function createCacheKey(): string {
      try {
        $identifier = urlencode(Headers::getBearerToken());

        return self::CACHE_KEY_NAMESPACE . "#$identifier";
      } catch (Throwable) {
      }

      $clientIp = urlencode(Headers::getClientIp());
      $userAgent = urlencode(Headers::getUserAgent());

      return self::CACHE_KEY_NAMESPACE . "#$clientIp#$userAgent";
    }
  }
}
