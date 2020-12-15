<?php

declare(strict_types=1);

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
  private FilesystemAdapter $filesystemCacheAdapter;

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
   * @return bool
   *   TRUE if the item was successfully removed,
   *   FALSE if there was any error.
   *
   * @throws \Psr\Cache\InvalidArgumentException
   */
  public function delete(): bool {
    return $this->filesystemCacheAdapter->delete(self::CACHE_ITEM_KEY);
  }

  /**
   * Prune the website data cache.
   *
   * @return bool
   *   TRUE if the cache was successfully pruned,
   *   FALSE if there was any error.
   */
  public function prune(): bool {
    return $this->filesystemCacheAdapter->prune();
  }

}
