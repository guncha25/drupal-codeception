<?php

namespace Codeception\Module;

use Codeception\Module;
use Codeception\Util\Drush;

/**
 * Class DrupalDrush.
 *
 * ### Example
 * #### Example (DrupalDrush)
 *     modules:
 *        - DrupalDrush:
 *          working_directory: './web'
 *          drush: './vendor/bin/drush'
 *
 * @package Codeception\Module
 */
class DrupalDrush extends Module {

  /**
   * Default module configuration.
   *
   * @var array
   */
  protected $config = [
    'drush' => 'drush',
  ];

  /**
   * Execute a drush command.
   *
   * @param string $command
   *   Command to run.
   *   e.g. "en devel -y".
   *
   * @return string
   *   The process output.
   */
  public function runDrush($command) {
    return Drush::runDrush($command, $this->_getConfig('drush'), $this->_getConfig('working_directory'));
  }

  /**
   * Gets login uri.
   *
   * @param string $name
   *   User id.
   *
   * @return bool|string
   *   Login uri.
   */
  public function getLoginUri($name = '') {
    $user = '';
    if (!empty($uid)) {
      $user = '--name=' . $name;
    }
    $gen_url = str_replace(PHP_EOL, '', $this->runDrush('uli ' . $user));

    return substr($gen_url, strpos($gen_url, '/user/reset'));
  }

}
