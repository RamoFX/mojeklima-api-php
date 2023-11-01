<?php



namespace App\Resources\Common\Utilities {

  class ConfigManager {
    public function __construct(
      private readonly array $config
    ) {}

    public function get(string $selector, mixed $default = null): mixed {
      $keys = explode('.', $selector);
      $configSlice = $this->config;

      foreach ($keys as $key) {
        if (isset($configSlice[$key])) {
          $configSlice = $configSlice[$key];
        } else {
          return $default;
        }
      }

      return $configSlice;
    }
  }
}
