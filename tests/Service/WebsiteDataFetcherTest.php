<?php

namespace App\Tests\Service;

use App\Service\WebsiteDataCache;
use App\Service\WebsiteDataFetcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class WebsiteDataFetcherTest.
 *
 * @package App\Tests\Service
 * @coversDefaultClass \App\Service\WebsiteDataFetcher
 */
class WebsiteDataFetcherTest extends TestCase {

  /**
   * The test website config.
   *
   * @var array
   */
  protected const TEST_WEBSITE_CONFIG = [
    [
      'name' => 'The Pizza Website',
      'url' => 'https://test.the-pizza-website.com',
      'basic_auth' => [
        'user' => 'pizza',
        'password' => 'margherita',
      ],
    ],
    [
      'name' => 'The Burger Website',
      'url' => 'https://test.the-burger-website.com',
      'basic_auth' => [
        'user' => 'cheese',
        'password' => 'burger',
      ],
    ]
  ];

  /**
   * The test website data.
   *
   * @var array
   */
  protected const TEST_WEBSITE_DATA = [
    [
      'app' => 'Symfony',
      'versions' => [
        'app' => '5.2',
        'php' => '7.4',
      ],
    ],
    [
      'app' => 'Drupal',
      'versions' => [
        'app' => '9.0.2',
        'php' => '7.4',
      ],
    ],
  ];

  /**
   * Get the HTTP client mock.
   *
   * @param array $websiteData
   *   The website data.
   * @param int $statusCode
   *   The status code.
   *
   * @return \Symfony\Contracts\HttpClient\HttpClientInterface
   *   The HTTP client mock.
   */
  protected function getHttpClientMock(array $websiteData = [], int $statusCode = Response::HTTP_OK): HttpClientInterface {
    $responseInterfaceMock = $this->getMockBuilder(ResponseInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Return always the same status code.
    $responseInterfaceMock->expects($this->any())
      ->method('getStatusCode')
      ->willReturn($statusCode);

    // Return the different website data for each request.
    // As the getStatusCode() method above also increases the index,
    // we need to start at 1 and increase in 2 steps.
    $i = 1;
    foreach ($websiteData as $data) {
      $responseInterfaceMock->expects($this->at($i))
        ->method('getContent')
        ->willReturn(json_encode($data));

      $i += 2;
    }

    $httpClientInterfaceMock = $this->getMockBuilder(HttpClientInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    // The request() method can always return the same response mock.
    $httpClientInterfaceMock->expects($this->any())
      ->method('request')
      ->willReturn($responseInterfaceMock);

    return $httpClientInterfaceMock;
  }

  /**
   * Get the parameter bag mock, which holds the website config.
   *
   * @param array $websitesConfig
   *   The website config.
   *
   * @return \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface
   *   The parameter bag mock.
   */
  protected function getParameterBagMock(array $websitesConfig = []): ContainerBagInterface {
    $parameterBagMock = $this->getMockBuilder(ContainerBagInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $parameterBagMock->method('get')->willReturn($websitesConfig);

    return $parameterBagMock;
  }

  /**
   * Get the website data cache mock.
   *
   * @return \App\Service\WebsiteDataCache
   *   The website data cache mock.
   */
  protected function getWebsiteDataCacheMock(): WebsiteDataCache {
    $websiteDataCacheMock = $this->getMockBuilder(WebsiteDataCache::class)
      ->disableOriginalConstructor()
      ->getMock();

    return $websiteDataCacheMock;
  }

  /**
   * @covers ::__construct
   */
  public function testWebsiteDataFetcher(): void {
    $websiteDataFetcher = new WebsiteDataFetcher(
      $this->getHttpClientMock(),
      $this->getParameterBagMock(['foo']),
      $this->getWebsiteDataCacheMock()
    );
    $this->assertInstanceOf(WebsiteDataFetcher::class, $websiteDataFetcher);

    // With no website data we should get an exception.
    $this->expectException(InvalidConfigurationException::class);
    $websiteDataFetcher = new WebsiteDataFetcher(
      $this->getHttpClientMock(),
      $this->getParameterBagMock(),
      $this->getWebsiteDataCacheMock()
    );
  }

  /**
   * @covers ::fetch
   */
  public function testFetch(): void {
    $websiteDataFetcher = new WebsiteDataFetcher(
      $this->getHttpClientMock(self::TEST_WEBSITE_DATA),
      $this->getParameterBagMock(self::TEST_WEBSITE_CONFIG),
      $this->getWebsiteDataCacheMock()
    );

    // Build the expected website data array.
    $expectedData = [];
    $i = 0;
    foreach (self::TEST_WEBSITE_CONFIG as $website) {
      $expectedData[$i] = [
        'name' => $website['name'],
        'url' => $website['url'],
      ];
      $expectedData[$i] += self::TEST_WEBSITE_DATA[$i];
      $i++;
    }

    $websiteData = $websiteDataFetcher->fetch(FALSE);
    $this->assertEquals($expectedData, $websiteData);
  }

}
