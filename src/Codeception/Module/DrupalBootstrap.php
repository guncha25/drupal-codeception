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
 *          site_path: 'sites/default'
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
    'site_path' => 'sites/default',
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
    chdir($this->_getConfig('root'));
    $request = Request::createFromGlobals();
    $autoloader = require $this->_getConfig('root') . '/autoload.php';
    $kernel = new TestDrupalKernel('prod', $autoloader, $this->_getConfig('root'));
    $kernel->bootTestEnvironment($this->_getConfig('site_path'));
    $kernel->prepareLegacyRequest($request);
  }

}
