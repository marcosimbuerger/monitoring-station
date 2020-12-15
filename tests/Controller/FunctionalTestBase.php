<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class FunctionalTestBase.
 *
 * @package App\Tests\Controller
 */
class FunctionalTestBase extends WebTestCase {

  /**
   * The KernelBrowser client.
   *
   * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
   */
  protected $kernelBrowserClient;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->kernelBrowserClient = static::createClient();
  }

  /**
   * Do a login.
   */
  protected function logIn(): void {
    /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
    $session = $this->kernelBrowserClient->getContainer()->get('session');

    // If we don't define multiple connected firewalls, the context defaults to the firewall name.
    // See: https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
    $firewallName = 'main';
    $firewallContext = 'main';

    $token = new UsernamePasswordToken('test-admin', NULL, $firewallName, ['ROLE_ADMIN']);
    $session->set('_security_' . $firewallContext, serialize($token));
    $session->save();

    $cookie = new Cookie($session->getName(), $session->getId());
    $this->kernelBrowserClient->getCookieJar()->set($cookie);
  }

}
