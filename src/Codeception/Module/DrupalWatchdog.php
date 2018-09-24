<?php

namespace Codeception\Module;

use Codeception\Module;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Component\Utility\Xss;

/**
 * Class DrupalWatchdog.
 *
 * @package Codeception\Module
 */
class DrupalWatchdog extends Module {

  /**
   * Default module configuration.
   *
   * @var array
   */
  protected $config = [
    'channels' => [
      'php' => 'notice',
    ],
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
    if (\Drupal::moduleHandler()->moduleExists('dblog')) {
      // Clear log entries from the database log.
      \Drupal::database()->truncate('watchdog')->execute();
    }
  }

  /**
   * Tear down after tests.
   */
  public function _afterSuite() { // @codingStandardsIgnoreLine
    if (\Drupal::moduleHandler()->moduleExists('dblog')) {
      foreach ($this->config['channels'] as $channel => $level) {
        if (is_string($level) && !empty($this->logLevels[strtoupper($level)])) {
          // Load any database log entries of level WARNING or more serious.
          $query = \Drupal::database()->select('watchdog', 'w');
          $query->fields('w', ['type', 'severity', 'message', 'variables'])
            ->condition('severity', $this->logLevels[strtoupper($level)], '<=')
            ->condition('type', $channel);
          $result = $query->execute();
          foreach ($result as $row) {
            // Build a readable message and declare a failure.
            $variables = @unserialize($row->variables);
            $message = $row->type . ' - ';
            $message .= RfcLogLevel::getLevels()[$row->severity] . ': ';
            $message .= t(Xss::filterAdmin($row->message), $variables)->render(); // @codingStandardsIgnoreLine
            $this->fail($message);
          }
        }
      }
    }
  }

}
