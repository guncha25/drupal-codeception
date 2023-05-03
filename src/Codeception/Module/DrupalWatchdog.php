<?php

namespace Codeception\Module;

use Codeception\Module;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Component\Utility\Xss;

/**
 * Class DrupalWatchdog.
 *
 * ### Example
 * #### Example (DrupalWatchdog)
 *     modules:
 *        - DrupalWatchdog:
 *           enabled: true
 *           level: 'ERROR'
 *           channels:
 *             my_module: 'NOTICE'
 *             php: 'WARNING'
 *
 * @package Codeception\Module
 */
class DrupalWatchdog extends Module {

  /**
   * Log messages.
   *
   * @var array
   */
  protected $messages = [];

  /**
   * Default module configuration.
   *
   * @var array
   */
  protected array $config = [
    'channels' => [],
    'level' => 'ERROR',
    'enabled' => TRUE,
  ];

  /**
   * Log levels.
   *
   * @var array
   */
  protected $logLevels = [
    'DEBUG' => 7,
    'INFO' => 6,
    'NOTICE' => 5,
    'WARNING' => 4,
    'ERROR' => 3,
    'CRITICAL' => 2,
    'ALERT' => 1,
    'EMERGENCY' => 0,
  ];

  /**
   * {@inheritdoc}
   */
  public function _beforeSuite($settings = []) { // @codingStandardsIgnoreLine
    if ($this->_getConfig('enabled')) {
      $this->prepareLogWatch();
    }
  }

  /**
   * Prepares log.
   */
  public function prepareLogWatch() {
    if (\Drupal::moduleHandler()->moduleExists('dblog')) {
      // Clear log entries from the database log.
      \Drupal::database()->truncate('watchdog')->execute();
    }
    else {
      $this->fail('Database logging is not enabled. Please enable dblog module to continue.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function _afterSuite() { // @codingStandardsIgnoreLine
    if ($this->_getConfig('enabled')) {
      $this->checkLogs();
    }
  }

  /**
   * Check log.
   *
   * @param array $settings
   *   Setting override.
   */
  public function checkLogs(array $settings = []) {
    $channels = isset($settings['channels']) ? $settings['channels'] : $this->_getConfig('channels');
    if (!empty($channels) && is_array($channels)) {
      foreach ($this->_getConfig('channels') as $channel => $level) {
        if (is_string($level) && isset($this->logLevels[strtoupper($level)])) {
          $this->processResult($this->getLogResults($this->logLevels[strtoupper($level)], $channel));
        }
      }
    }
    $level = isset($settings['level']) ? $settings['level'] : $this->_getConfig('level');
    if (is_string($level) && isset($this->logLevels[strtoupper($level)])) {
      $this->processResult($this->getLogResults($this->logLevels[strtoupper($level)]));
    }
    if (!empty($this->messages)) {
      $this->fail(implode(PHP_EOL, $this->messages));
    }
  }

  /**
   * Returns query result of log messages.
   *
   * @param int $level
   *   Log level.
   * @param string $channel
   *   Log channel.
   *
   * @return \Drupal\Core\Database\StatementInterface|null
   *   Query result.
   */
  private function getLogResults($level, $channel = NULL) {
    $query = \Drupal::database()->select('watchdog', 'w');
    $query->fields('w', ['type', 'severity', 'message', 'variables'])
      ->condition('severity', $level, '<=');

    if ($channel) {
      $query->condition('type', $channel);
    }
    return $query->execute();
  }

  /**
   * Process log results.
   *
   * @param mixed $result
   *   Query result.
   */
  protected function processResult($result) {
    foreach ($result as $row) {
      // Build a readable message and declare a failure.
      $variables = @unserialize($row->variables);
      $message = $row->type . ' - ';
      $message .= RfcLogLevel::getLevels()[$row->severity] . ': ';
      $message .= t(Xss::filterAdmin($row->message), $variables)->render(); // @codingStandardsIgnoreLine
      $this->messages[] = $message;
    }
  }

}
