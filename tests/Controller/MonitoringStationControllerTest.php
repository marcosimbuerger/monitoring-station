<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MonitoringStationControllerTest.
 *
 * @package App\Tests\Controller
 * @coversDefaultClass \App\Controller\MonitoringStationController
 */
class MonitoringStationControllerTest extends FunctionalTestBase {

  /**
   * The login page.
   *
   * @covers ::login
   */
  public function testLogin(): void {
    $this->kernelBrowserClient->request(
      Request::METHOD_GET,
      '/login'
    );

    $this->assertEquals(Response::HTTP_OK, $this->kernelBrowserClient->getResponse()->getStatusCode());
  }

  /**
   * Test dashboard page in a log in state.
   *
   * @covers ::dashboard
   */
  public function testDashboard(): void {
    // Without login, we get 'found', but not the dashboard.
    $this->kernelBrowserClient->request(
      Request::METHOD_GET,
      '/'
    );
    $this->assertEquals(Response::HTTP_FOUND, $this->kernelBrowserClient->getResponse()->getStatusCode());

    // Now, do a login.
    $this->logIn();
    $this->kernelBrowserClient->request(
      Request::METHOD_GET,
      '/'
    );
    $this->assertEquals(Response::HTTP_OK, $this->kernelBrowserClient->getResponse()->getStatusCode());
  }

}
