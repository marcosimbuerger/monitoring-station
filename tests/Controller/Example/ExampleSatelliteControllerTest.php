<?php

declare(strict_types=1);

namespace App\Tests\Controller\Example;

use App\Tests\Controller\FunctionalTestBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExampleSatelliteControllerTest.
 *
 * See also:
 * - config/routes.yaml
 * - config/packages/security.yaml
 *
 * @package App\Tests\Controller\Example
 * @coversDefaultClass \App\Controller\Example\ExampleSatelliteController
 */
class ExampleSatelliteControllerTest extends FunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    // The example basic auth 'foo'/'bar' is defined in security.yaml.
    $this->kernelBrowserClient = static::createClient([], [
      'PHP_AUTH_USER' => 'foo',
      'PHP_AUTH_PW' => 'bar'
    ]);
  }

  /**
   * Test the get() request.
   *
   * @covers ::get
   */
  public function testGet(): void {
    $this->kernelBrowserClient->request(
      Request::METHOD_GET,
      '/example/monitoring-satellite/v1/get'
    );
    /** @var \Symfony\Component\HttpFoundation\JsonResponse $response */
    $response = $this->kernelBrowserClient->getResponse();

    // As defined in \App\Controller\Example\ExampleSatelliteController.
    $expectedResult = json_encode([
      'app' => 'Drupal',
      'versions' => [
        'app' => '9.0.2',
        'php' => '7.4',
      ],
    ]);

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertEquals($expectedResult, $response->getContent());
  }

}
