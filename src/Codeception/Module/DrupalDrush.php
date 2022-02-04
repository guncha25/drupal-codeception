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
 *          timeout: 120
 *          drush: './vendor/bin/drush'
 *          alias: '@mysite.com'
 *          options:
 *            uri: http://mydomain.com
 *            root: /app/web
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
    'alias' => '',
    'options' => [],
  ];

  /**
   * Execute a drush command.
   *
   * @param string $command
   *   Command to run.
   *   e.g. "en devel -y".
   * @param array $options
   *   Associative array of options.
   * @param bool $return_process
   *   If TRUE, the Process object will be returned. If false, the output of
   *   Process::getOutput() will be returned. Defaults to FALSE.
   *
   * @return string|\Symfony\Component\Process\Process
   *   The process output, or the process itself.
   */
  public function runDrush($command, array $options = [], $return_process = FALSE) {
    if ($alias = $this->_getConfig('alias')) {
      $command = $alias . ' ' . $command;
    }
    if (!empty($options)) {
      $command = $this->normalizeOptions($options) . $command;
    }
    elseif ($this->_getConfig('options')) {
      $command = $this->normalizeOptions($this->_getConfig('options')) . $command;
    }
    return Drush::runDrush($command, $this->_getConfig('drush'), $this->_getConfig('working_directory'), $this->_getConfig('timeout'), $return_process);
  }

  /**
   * Returns options as sting.
   *
   * @param array $options
   *   Associative array of options.
   *
   * @return string
   *    Sring of options.
   */
  protected function normalizeOptions(array $options) {
    $command = '';
    foreach ($options as $key => $value) {
      if (is_string($value)) {
        $command .= '--' . $key . '=' . $value . ' ';
      }
    }
    return $command;
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
    if (!empty($name)) {
      $user = '--name=' . $name;
    }
    $gen_url = str_replace(PHP_EOL, '', $this->runDrush('uli ' . $user));

    return substr($gen_url, strpos($gen_url, '/user/reset'));
  }

}
