<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\WebsiteDataCache;
use App\Service\WebsiteDataFetcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
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
   * Get the logger mock.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger mock.
   */
  protected function getLoggerMock(): LoggerInterface {
    return $this->getMockBuilder(LoggerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
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
   * Build the expected valid website data result.
   *
   * @return array
   *   The expected website data result.
   */
  protected function getExpectedWebsiteDataResult(): array {
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

    return $expectedData;
  }

  /**
   * @covers ::__construct
   */
  public function testWebsiteDataFetcher(): void {
    // With valid data, we should get a valid WebsiteDataFetcher object.
    $websiteDataFetcher = new WebsiteDataFetcher(
      $this->getHttpClientMock(),
      $this->getParameterBagMock(['foo']),
      $this->getLoggerMock(),
      $this->getWebsiteDataCacheMock()
    );
    $this->assertInstanceOf(WebsiteDataFetcher::class, $websiteDataFetcher);

    // With no website data, we should get an exception.
    $this->expectException(InvalidConfigurationException::class);
    $websiteDataFetcher = new WebsiteDataFetcher(
      $this->getHttpClientMock(),
      $this->getParameterBagMock(),
      $this->getLoggerMock(),
      $this->getWebsiteDataCacheMock()
    );
  }

  /**
   * Test fetch() with an invalid website config.
   *
   * The website data result will only contain data for websites,
   * which have a valid config (all mandatory values set).
   * If the website config is not valid, it will not do a api request for this website.
   *
   * @covers ::fetch
   */
  public function testFetchWithInvalidWebsiteConfig(): void {
    $websiteConfig = self::TEST_WEBSITE_CONFIG;
    // Remove a mandatory config value.
    unset($websiteConfig[0]['basic_auth']['user']);

    // As the first config is invalid, the api request will only done once,
    // which then will return the data of our 2nd test data.
    // Therefore, remove website data [0] from our test data.
    $websiteData = self::TEST_WEBSITE_DATA;
    unset($websiteData[0]);

    $websiteDataFetcher = new WebsiteDataFetcher(
      $this->getHttpClientMock($websiteData),
      $this->getParameterBagMock($websiteConfig),
      $this->getLoggerMock(),
      $this->getWebsiteDataCacheMock()
    );

    // As the config of the 1st website is not valid,
    // it will not be in the website data result.
    $expectedData = $expectedData = $this->getExpectedWebsiteDataResult();
    unset($expectedData[0]);
    $expectedData = array_values($expectedData);

    $websiteData = $websiteDataFetcher->fetch(FALSE);

    // We should get the website data result for the 2nd website.
    $this->assertCount(1, $websiteData);
    $this->assertEquals($expectedData, $websiteData);
  }

  /**
   * Test fetch() with extended website data.
   *
   * The monitoring satellite will return more values as needed (for whatever reason).
   * The website data result should only contain website data,
   * which are defined by the website data schema (WebsiteDataFetcher::VALID_WEBSITE_DATA_SCHEMA).
   *
   * @covers ::fetch
   */
  public function testFetchWithExtendedWebsiteData(): void {
    $websiteTestData = self::TEST_WEBSITE_DATA;
    // Add additional data to the website data response.
    $websiteTestData[0]['foo'] = 'XYZ';
    $websiteTestData[0]['versions']['bar'] = 100;

    $websiteDataFetcher = new WebsiteDataFetcher(
      $this->getHttpClientMock($websiteTestData),
      $this->getParameterBagMock(self::TEST_WEBSITE_CONFIG),
      $this->getLoggerMock(),
      $this->getWebsiteDataCacheMock()
    );

    $expectedData = $expectedData = $this->getExpectedWebsiteDataResult();

    $websiteData = $websiteDataFetcher->fetch(FALSE);

    // We should get the website data result for both websites,
    // but for the first website, the additionally added values
    // 'foo' and 'bar' should be removed.
    $this->assertCount(2, $websiteData);
    $this->assertFalse(isset($websiteData[0]['foo']));
    $this->assertFalse(isset($websiteData[0]['versions']['bar']));
    $this->assertEquals($expectedData, $websiteData);
  }

  /**
   * Test fetch() with empty website data.
   *
   * If the monitoring satellite returns no data.
   *
   * @covers ::fetch
   */
  public function testFetchWithEmptyWebsiteData(): void {
    $websiteDataFetcher = new WebsiteDataFetcher(
      $this->getHttpClientMock([[], []]),
      $this->getParameterBagMock(self::TEST_WEBSITE_CONFIG),
      $this->getLoggerMock(),
      $this->getWebsiteDataCacheMock()
    );

    // We should get an empty array.
    $websiteData = $websiteDataFetcher->fetch(FALSE);
    $this->assertEquals([], $websiteData);
  }

  /**
   * Test fetch() with an successful example.
   *
   * @covers ::fetch
   */
  public function testFetch(): void {
    $websiteDataFetcher = new WebsiteDataFetcher(
      $this->getHttpClientMock(self::TEST_WEBSITE_DATA),
      $this->getParameterBagMock(self::TEST_WEBSITE_CONFIG),
      $this->getLoggerMock(),
      $this->getWebsiteDataCacheMock()
    );

    $expectedData = $this->getExpectedWebsiteDataResult();

    // We should get the website data result for both websites.
    $websiteData = $websiteDataFetcher->fetch(FALSE);
    $this->assertCount(2, $websiteData);
    $this->assertEquals($expectedData, $websiteData);
  }

}
