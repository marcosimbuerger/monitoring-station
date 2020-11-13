<?php

namespace App\Controller\Example;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ExampleSatelliteController.
 *
 * @package App\Controller\Example
 */
class ExampleSatelliteController {

  /**
   * Returns some demo content.
   * This route is secured with a basic auth.
   * See: security.yaml.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  public function get(): JsonResponse {
    return new JsonResponse([
      'cms' => 'Drupal',
      'cms_version' => '9.0.2',
      'php_version' => '7.4',
    ]);
  }

}
