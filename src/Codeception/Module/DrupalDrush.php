<?php

namespace Codeception\Module;

use Codeception\Module;
use Codeception\Util\Drush;

/**
 * Class DrupalDrush.
 *
 * @package Codeception\Module
 */
class DrupalDrush extends Module {

  /**
   * Execute a drush command.
   *
   * @param string $command
   *   Command to run.
   *   e.g. "en devel -y".
   * @param string $drush
   *   The drush command to use.
   *
   * @return string
   *   The process output.
   */
  public function runDrush($command, $drush = 'drush') {
    return Drush::runDrush($command, $drush, $this->_getConfig('working_directory'));
  }

  /**
   * Gets login uri.
   *
   * @param string $uid
   *   User id.
   *
   * @return bool|string
   *   Login uri.
   */
  public function getLoginUri($uid = '') {
    $user = '';
    if (!empty($uid)) {
      $user = '--uid=' . $uid;
    }
    $gen_url = str_replace(PHP_EOL, '', $this->runDrush('uli ' . $user));

    return substr($gen_url, strpos($gen_url, '/user/reset'));
  }

}
