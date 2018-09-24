<?php

namespace Codeception\Module;

use Codeception\Module;
use Codeception\TestCase;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Component\Utility\Xss;

/**
 * Class DrupalWatchdog.
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
  protected $config = [
    'channels' => [],
    'level' => 'ERROR',
    'after_test' => TRUE,
    'after_suite' => FALSE,
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
    if ($this->_getConfig('after_suite')) {
      $this->prepareLogWatch();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function _before(TestCase $test) { // @codingStandardsIgnoreLine
    if ($this->_getConfig('after_test')) {
      $this->prepareLogWatch();
    }
  }

  public function prepareLogWatch() {
    if (\Drupal::moduleHandler()->moduleExists('dblog')) {
      // Clear log entries from the database log.
      \Drupal::database()->truncate('watchdog')->execute();
    }
    else {
      $this->fail('Database loging is not enabled.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function _afterSuite() { // @codingStandardsIgnoreLine
    if ($this->_getConfig('after_suite')) {
      $this->checkLogs();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function _after(TestCase $test) { // @codingStandardsIgnoreLine
    if ($this->_getConfig('after_test')) {
      $this->checkLogs();
    }
  }

  public function checkLogs() {
    $channels = $this->_getConfig('channels');
    if (!empty($channels) && is_array($channels)) {
      foreach ($this->_getConfig('channels') as $channel => $level) {
        if (is_string($level) && isset($this->logLevels[strtoupper($level)])) {
          $this->processResult($this->getLogResults($this->logLevels[strtoupper($level)], $channel));
        }
      }
    }
    $level = $this->_getConfig('level');
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
    $messages = [];
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
