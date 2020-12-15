<?php

declare(strict_types=1);

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
   * The valid website config schema.
   *
   * @var array
   */
  private const WEBSITE_CONFIG_SCHEMA = [
    'name' => '',
    'url' => '',
    'basic_auth' => [
      'user' => '',
      'password' => '',
    ],
  ];

  /**
   * The valid website data schema.
   *
   * @var array
   */
  private const VALID_WEBSITE_DATA_SCHEMA = [
    'app' => '',
    'versions' => [
      'app' => '',
      'php' => '',
    ],
  ];

  /**
   * The HTTP client.

   * @var \Symfony\Contracts\HttpClient\HttpClientInterface
   */
  private HttpClientInterface $httpClient;

  /**
   * The website config.
   *
   * @var array
   */
  private array $websitesConfig;

  /**
   * The website data cache.
   *
   * @var \App\Service\WebsiteDataCache
   */
  private WebsiteDataCache $websiteDataCache;

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
        $websiteData = $this->fetchWebsiteData();

        if (empty($websiteData)) {
          // Do not cache empty response.
          $item->expiresAfter(0);
        }
        else {
          $item->expiresAfter(WebsiteDataCache::CACHE_LIFE_TIME);
        }

        return $websiteData;
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
   * @param array $websiteConfigSchema
   *   The schema of the valid website config.
   * @param bool $result
   *   The result, which will be returned.
   *
   * @return bool
   *   TRUE if all are valid,
   *   FALSE otherwise.
   */
  private function validateWebsiteConfig(array $websiteConfig, array $websiteConfigSchema = self::WEBSITE_CONFIG_SCHEMA, bool $result = TRUE): bool {
    foreach ($websiteConfigSchema as $key => $value) {
      if (is_array($value) && is_array($websiteConfig[$key]) && $result !== FALSE) {
        $result = $this->validateWebsiteConfig($websiteConfig[$key], $websiteConfigSchema[$key]);
      }
      elseif ($result !== FALSE) {
        if (isset($websiteConfig[$key]) && !empty($websiteConfig[$key])) {
          $result = TRUE;
        }
        else {
          $result = FALSE;
        }
      }
    }

    return $result;
  }

  /**
   * Validate the website response data.
   *
   * @param array $responseData
   *   The response data, retrieved from the api.
   * @param array $dataSchema
   *   The data schema, which is used to validate the response data.
   * @param array $validatedData
   *   The validated response data.
   *
   * @return array
   *   The validated response data.
   */
  private function validateWebsiteResponseData(array $responseData, array $dataSchema = self::VALID_WEBSITE_DATA_SCHEMA, array $validatedData = []): array {
    foreach ($dataSchema as $key => $value) {
      if (is_array($value)) {
        $validatedData[$key] = $this->validateWebsiteResponseData($responseData[$key], $dataSchema[$key]);
      }
      else {
        if (isset($responseData[$key]) && !empty($responseData[$key])) {
          $validatedData[$key] = strip_tags($responseData[$key]);
        }
      }
    }

    return $validatedData;
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
