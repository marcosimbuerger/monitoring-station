<?php

namespace App\Service;

use App\Service\WebsiteDataCache;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class WebsiteDataFetcher.
 *
 * @package App\Service
 */
class WebsiteDataFetcher {

  /**
   * The website config parameter name.
   *
   * @var string
   */
  private const WEBSITES_CONFIG_PARAMETER_NAME = 'monitoring_satellite.websites';

  /**
   * The monitoring satellite endpoint.
   *
   * @var string
   */
  private const MONITORING_SATELLITE_ENDPOINT = '/monitoring-satellite/v1/get';

  /**
   * The valid website data keys.
   *
   * @var array
   */
  private const VALID_WEBSITE_DATA_KEYS = [
    'cms',
    'cms_version',
    'php_version',
  ];

  /**
   * The HTTP client.

   * @var \Symfony\Contracts\HttpClient\HttpClientInterface
   */
  private $httpClient;

  /**
   * The website config.
   *
   * @var \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface
   */
  private $websitesConfig;

  /**
   * The website data cache.
   *
   * @var \App\Service\WebsiteDataCache
   */
  private $websiteDataCache;

  /**
   * WebsiteDataFetcher constructor.
   *
   * @param \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient
   *   The HTTP client.
   * @param \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface $parameterBag
   *   The website config.
   * @param \App\Service\WebsiteDataCache $websiteDataCache
   *   The website data cache.
   */
  public function __construct(HttpClientInterface $httpClient, ContainerBagInterface $parameterBag, WebsiteDataCache $websiteDataCache) {
    $this->httpClient = $httpClient;
    $this->websiteDataCache = $websiteDataCache;

    if ($websitesConfig = $parameterBag->get(self::WEBSITES_CONFIG_PARAMETER_NAME)) {
      $this->websitesConfig = $websitesConfig;
    }
    else {
      throw new InvalidConfigurationException('Could not load websites configuration for "' . self::WEBSITES_CONFIG_PARAMETER_NAME . '".');
    }
  }

  /**
   * Fetch the website data.
   *
   * @param bool $cache
   *   Defines if cache should be used.
   *
   * @return array
   *   The website data.
   *
   * @throws \Psr\Cache\InvalidArgumentException
   */
  public function fetch(bool $cache = TRUE): array {
    if ($cache === TRUE) {
      return $this->websiteDataCache->getAdapter()->get(WebsiteDataCache::CACHE_ITEM_KEY, function (ItemInterface $item) {
        $item->expiresAfter(WebsiteDataCache::CACHE_LIFE_TIME);
        return $this->fetchWebsiteData();
      });
    }

    // Fallback without cache.
    return $this->fetchWebsiteData();
  }

  /**
   * Fetch the website data.
   *
   * @return array
   *   The website data.
   */
  private function fetchWebsiteData(): array {
    $data = [];

    $i = 0;
    foreach ($this->websitesConfig as $website) {
      if ($this->validateWebsiteConfig($website)) {
        if ($responseData = $this->doApiRequest($website['url'], $website['basic_auth'])) {
          $data[$i]['name'] = $website['name'];
          $data[$i]['url'] = $website['url'];
          $data[$i] += $this->validateWebsiteResponseData($responseData);
          $i++;
        }
      }
    }

    return $data;
  }

  /**
   * Validate the website config.
   *
   * @param array $websiteConfig
   *   The website config.
   * @return bool
   *   TRUE if all are valid, FALSE otherwise.
   */
  private function validateWebsiteConfig(array $websiteConfig): bool {
    if (
      isset($websiteConfig['url']) && !empty($websiteConfig['url']) &&
      isset($websiteConfig['name']) && !empty($websiteConfig['name']) &&
      isset($websiteConfig['basic_auth']['user']) && !empty($websiteConfig['basic_auth']['user']) &&
      isset($websiteConfig['basic_auth']['password']) && !empty($websiteConfig['basic_auth']['password'])
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Validate the website response data.
   *
   * @param array $responseData
   *   The response data, retrieved from the api.
   *
   * @return array
   *   The validated response data.
   */
  private function validateWebsiteResponseData(array $responseData): array {
    $data = [];

    foreach (self::VALID_WEBSITE_DATA_KEYS as $key) {
      if (isset($responseData[$key]) && !empty($responseData[$key])) {
        $data[$key] = strip_tags($responseData[$key]);
      }
    }

    return $data;
  }

  /**
   * Does an API request to the given url and basic auth credentials.
   *
   * @param string $url
   *   The url.
   * @param array $basicAuth
   *   The basic auth credentials.
   *
   * @return array|null
   *   The response data.
   *   NULL if something went wrong.
   *
   * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
   * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
   * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
   * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
   */
  private function doApiRequest(string $url, array $basicAuth): ?array {
    $response = $this->httpClient->request(
      Request::METHOD_GET,
      $url . self::MONITORING_SATELLITE_ENDPOINT,
      [
        'auth_basic' => [$basicAuth['user'], $basicAuth['password']],
      ]
    );

    if ($response->getStatusCode() === Response::HTTP_OK) {
      if ($content = $response->getContent()) {
        return json_decode($content, TRUE);
      }
    }

    return NULL;
  }

}
