<?php

namespace App\Controller;

use App\Service\WebsiteDataFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class MonitoringStationController.
 *
 * @package App\Controller
 */
class MonitoringStationController extends AbstractController {

  /**
   * Handles the login request.
   *
   * @param \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils
   *   The authentication utils.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function login(AuthenticationUtils $authenticationUtils): Response {
    // Get the login error if there is one.
    $error = $authenticationUtils->getLastAuthenticationError();

    // Last username entered by the user.
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('login.html.twig', [
      'lastUsername' => $lastUsername,
      'error' => $error,
    ]);
  }

  /**
   * Creates the dashboard.
   *
   * @param \App\Services\WebsiteDataFetcher $websiteDataFetcher
   *   The website data fetcher.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   *
   * @throws \Psr\Cache\InvalidArgumentException
   */
  public function dashboard(WebsiteDataFetcher $websiteDataFetcher): Response {
    return $this->render('index.html.twig', [
      'websiteData' => $websiteDataFetcher->fetch()
    ]);
  }

}
