<?php

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * Class WebsiteDataCache.
 *
 * @package App\Service
 */
class WebsiteDataCache {

  /**
   * The key of the item to retrieve from the cache.
   *
   * @var string
   */
  public const CACHE_ITEM_KEY = 'monitoring_satellite_websites_data';

  /**
   * The cache life time.
   *
   * @var int
   */
  public const CACHE_LIFE_TIME = 3600;

  /**
   * The file system cache adapter.
   *
   * @var \Symfony\Component\Cache\Adapter\FilesystemAdapter
   */
  private $filesystemCacheAdapter;

  /**
   * WebsiteDataCache constructor.
   */
  public function __construct() {
    $this->filesystemCacheAdapter = new FilesystemAdapter();
  }

  /**
   * Get the file system cache adapter.
   *
   * @return \Symfony\Component\Cache\Adapter\FilesystemAdapter
   *   The file system cache adapter.
   */
  public function getAdapter(): FilesystemAdapter {
    return $this->filesystemCacheAdapter;
  }

  /**
   * Delete the website data cache.
   *
   * @throws \Psr\Cache\InvalidArgumentException
   */
  public function delete(): void {
    $this->filesystemCacheAdapter->delete(self::CACHE_ITEM_KEY);
  }

  /**
   * Prune the website data cache.
   */
  public function prune(): void {
    $this->filesystemCacheAdapter->prune();
  }

}
