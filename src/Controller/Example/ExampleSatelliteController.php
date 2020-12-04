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
   *   The JSON response.
   */
  public function get(): JsonResponse {
    return new JsonResponse([
      'app' => 'Drupal',
      'versions' => [
        'app' => '9.0.2',
        'php' => '7.4',
      ],
    ]);
  }

}
