<?php

namespace Codeception\Module;

use Codeception\Module;
use Symfony\Component\Process\ProcessBuilder;

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
    $command_args = array_merge([$drush], explode(' ', $command));
    $processBuilder = new ProcessBuilder($command_args);

    // Set working directory if configured.
    if ($pwd = $this->_getConfig('working_directory')) {
      $processBuilder->setWorkingDirectory($pwd);
    }

    return $processBuilder->getProcess()->mustRun()->getOutput();
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
