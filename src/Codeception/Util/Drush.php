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
   * @param int|float $timeout.
   *   Drush execution timeout.
   * @param bool $return_process
   *   If TRUE, the Process object will be returned. If false, the output of
   *   Process::getOutput() will be returned. Defaults to FALSE.
   *
   * @return string|\Symfony\Component\Process\Process
   *   The process output, or the process object itself.
   */
  public static function runDrush($command, $drush = 'drush', $pwd = NULL, $timeout = NULL, $return_process = FALSE) {
    $command_args = array_merge([$drush], explode(' ', $command));
    $process = new Process($command_args);

    // Set working directory if configured.
    if ($pwd) {
      $process->setWorkingDirectory($pwd);
    }

    // Set timeout if configured.
    if (isset($timeout)) {
      $process->setTimeout($timeout);
    }

    $process->mustRun();

    return $return_process ? $process : $process->getOutput();
  }

}
