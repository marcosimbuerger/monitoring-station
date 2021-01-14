<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

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
  private const CACHE_ITEM_KEY = 'monitoring_satellite_websites_data';

  /**
   * The cache lifetime.
   *
   * @var int
   */
  private int $cacheLifeTime;

  /**
   * The file system cache adapter.
   *
   * @var \Symfony\Component\Cache\Adapter\FilesystemAdapter
   */
  private FilesystemAdapter $filesystemCacheAdapter;

  /**
   * WebsiteDataCache constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface $parameterBag
   *   The parameter bag.
   */
  public function __construct(ContainerBagInterface $parameterBag) {
    $this->filesystemCacheAdapter = new FilesystemAdapter();

    $this->cacheLifeTime = 3600;
    if ($cacheLifeTime = $parameterBag->get('website_data.cache_life_time')) {
      $this->cacheLifeTime = $cacheLifeTime;
    }
  }

  /**
   * Get the cache item key.
   *
   * @return string
   *   The cache item key.
   */
  public function getCacheItemKey(): string {
    return self::CACHE_ITEM_KEY;
  }

  /**
   * Get the cache lifetime value.
   *
   * @return int
   *   The cache lifetime value.
   */
  public function getCacheLifeTime(): int {
    return $this->cacheLifeTime;
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
