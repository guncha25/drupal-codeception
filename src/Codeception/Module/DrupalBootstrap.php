<?php

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\DrupalKernel;

/**
 * Class DrupalBootstrap.
 *
 * @package Codeception\Module
 */
class DrupalBootstrap extends Module {

  /**
   * Default module configuration.
   *
   * @var array
   */
  protected $config = [
    'SERVER_PORT' => NULL,
    'REQUEST_URI' => '/',
    'REMOTE_ADDR' => '127.0.0.1',
    'REQUEST_METHOD' => 'GET',
    'SERVER_SOFTWARE' => NULL,
    'HTTP_USER_AGENT' => NULL,
    'HTTP_HOST' => NULL,
  ];

  /**
   * DrupalBootstrap constructor.
   *
   * @param \Codeception\Lib\ModuleContainer $container
   *   Module container.
   * @param null|array $config
   *   Array of configurations or null.
   *
   * @throws \Codeception\Exception\ModuleConfigException
   * @throws \Codeception\Exception\ModuleException
   */
  public function __construct(ModuleContainer $container, $config = NULL) {
    parent::__construct($container, $config);
    if (!isset($this->config['root'])) {
      $this->_setConfig(['root' => Configuration::projectDir() . 'web']);
    }

    $_SERVER['SERVER_PORT'] = $this->config['SERVER_PORT'];
    $_SERVER['REQUEST_URI'] = $this->config['REQUEST_URI'];
    $_SERVER['REMOTE_ADDR'] = $this->config['REMOTE_ADDR']; // @codingStandardsIgnoreLine
    $_SERVER['REQUEST_METHOD'] = $this->config['REQUEST_METHOD'];
    $_SERVER['SERVER_SOFTWARE'] = $this->config['SERVER_SOFTWARE'];
    $_SERVER['HTTP_USER_AGENT'] = $this->config['HTTP_USER_AGENT'];
    $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'] . 'index.php';
    $_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'];
    $_SERVER['SCRIPT_FILENAME'] = $this->config['root'] . '/index.php';
    if (isset($this->config['HTTP_HOST'])) {
      $_SERVER['HTTP_HOST'] = $this->config['HTTP_HOST'];
    }
    $request = Request::createFromGlobals();
    $autoloader = require $this->config['root'] . '/autoload.php';
    $kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
    try {
      $kernel->boot();
    }
    catch (\Exception $e) {
      $this->fail($e->getMessage());
    }
    $kernel->prepareLegacyRequest($request);
  }

}
