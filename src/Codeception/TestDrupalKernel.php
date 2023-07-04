<?php

namespace Codeception;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Drupal\Core\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class TestDrupalKernel extends DrupalKernel{

  /**
   * TestDrupalKernel constructor.
   */
  public function __construct($env, $class_loader, $root){
    $this->root = $root;
    parent::__construct($env, $class_loader);
  }

  /**
   * Boots test environment.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param string $sitePath
   *   The current site path.
   *
   * @throws \Exception
   */
  public function bootTestEnvironment($sitePath, Request $request){
    static::bootEnvironment();
    $this->setSitePath($sitePath);
    $this->loadLegacyIncludes();
    Settings::initialize($this->root, $sitePath, $this->classLoader);
    $this->boot();
    $this->preHandle($request);
    if (PHP_SAPI !== 'cli') {
      $request->setSession($this->container->get('session'));
    }
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, new Route('<none>'));
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, '<none>');
    $this->container->get('request_stack')->push($request);
    $this->container->get('router.request_context')->fromRequest($request);
  }

}
