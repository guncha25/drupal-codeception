<?php

namespace Codeception\Util;

use Symfony\Component\Process\Process;

/**
 * Class Drush.
 *
 * @package Codeception\Util
 */
class Drush {

  /**
   * Execute a drush command.
   *
   * @param string $command
   *   Command to run.
   *   e.g. "en devel -y".
   * @param string $drush
   *   The drush command to use.
   * @param string $pwd
   *   Working directory.
   *
   * @return string
   *   The process output.
   */
  public static function runDrush($command, $drush = 'drush', $pwd = NULL) {
    $command_args = array_merge([$drush], explode(' ', $command));
    $process = new Process($command_args);

    // Set working directory if configured.
    if ($pwd) {
      $process->setWorkingDirectory($pwd);
    }

    return $process->mustRun()->getOutput();
  }

}
