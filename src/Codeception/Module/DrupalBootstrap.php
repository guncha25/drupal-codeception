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
 * ### Example
 * #### Example (DrupalBootstrap)
 *     modules:
 *        - DrupalBootstrap:
 *          root: './web'
 *          server:
 *            SERVER_PORT: null
 *            REQUEST_URI: '/'
 *            REMOTE_ADDR: '127.0.0.1'
 *            REQUEST_METHOD: 'GET
 *            HTTP_HOST: 'site.multi'
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
    'server' => [
      'REQUEST_URI' => '/',
      'REMOTE_ADDR' => '127.0.0.1',
      'REQUEST_METHOD' => 'GET',
    ],
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
    foreach ($this->_getConfig('server') as $key => $value) {
      if (!is_null($value)) {
        $_SERVER[$key] = $value;
      }
    }
    $request = Request::createFromGlobals();
    $autoloader = require $this->_getConfig('root') . '/autoload.php';
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
