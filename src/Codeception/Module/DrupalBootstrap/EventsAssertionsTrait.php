<?php

declare(strict_types=1);

namespace Codeception\Module\DrupalBootstrap;

use Drupal\webprofiler\DataCollector\EventsDataCollector;
use Drupal\webprofiler\EventDispatcher\EventDispatcherTraceableInterface;
use function get_class;
use function is_array;
use function is_object;

/**
 *
 */
trait EventsAssertionsTrait {

  /**
   * Verifies that there were no orphan events during the test.
   *
   * An orphan event is an event that was triggered by manually executing the
   * [`dispatch()`](https://symfony.com/doc/current/components/event_dispatcher.html#dispatch-the-event) method
   * of the EventDispatcher but was not handled by any listener after it was dispatched.
   *
   * ```php
   * <?php
   * $I->dontSeeOrphanEvent();
   * $I->dontSeeOrphanEvent('App\MyEvent');
   * $I->dontSeeOrphanEvent(new App\Events\MyEvent());
   * $I->dontSeeOrphanEvent(['App\MyEvent', 'App\MyOtherEvent']);
   * ```
   *
   * @param string|object|string[] $expected
   */
  public function dontSeeOrphanEvent($expected = NULL): void {
    $eventCollector = $this->grabEventCollector();

    $data = $eventCollector->getOrphanedEvents();
    $expected = is_array($expected) ? $expected : [$expected];

    if ($expected === NULL) {
      $this->assertSame(0, count($data));
    }
    else {
      $this->assertEventNotTriggered($data, $expected);
    }
  }

  /**
   * Verifies that one or more event listeners were not called during the test.
   *
   * ```php
   * <?php
   * $I->dontSeeEventTriggered('App\MyEvent');
   * $I->dontSeeEventTriggered(new App\Events\MyEvent());
   * $I->dontSeeEventTriggered(['App\MyEvent', 'App\MyOtherEvent']);
   * $I->dontSeeEventTriggered('my_event_string_name');
   * $I->dontSeeEventTriggered(['my_event_string', 'my_other_event_string]);
   * ```
   *
   * @param string|object|string[] $expected
   */
  public function dontSeeEventTriggered($expected): void {
    $eventCollector = $this->grabEventCollector();

    $data = $eventCollector->getCalledListeners();
    $expected = is_array($expected) ? $expected : [$expected];

    $this->assertEventNotTriggered($data, $expected);
  }

  /**
   * Verifies that one or more orphan events were dispatched during the test.
   *
   * An orphan event is an event that was triggered by manually executing the
   * [`dispatch()`](https://symfony.com/doc/current/components/event_dispatcher.html#dispatch-the-event) method
   * of the EventDispatcher but was not handled by any listener after it was dispatched.
   *
   * ```php
   * <?php
   * $I->seeOrphanEvent('App\MyEvent');
   * $I->seeOrphanEvent(new App\Events\MyEvent());
   * $I->seeOrphanEvent(['App\MyEvent', 'App\MyOtherEvent']);
   * $I->seeOrphanEvent('my_event_string_name');
   * $I->seeOrphanEvent(['my_event_string_name', 'my_other_event_string]);
   * ```
   *
   * @param string|object|string[] $expected
   */
  public function seeOrphanEvent($expected): void {
    $eventCollector = $this->grabEventCollector();

    $data = $eventCollector->getOrphanedEvents();
    $expected = is_array($expected) ? $expected : [$expected];

    $this->assertEventTriggered($data, $expected);
  }

  /**
   * Verifies that one or more event listeners were called during the test.
   *
   * ```php
   * <?php
   * $I->seeEventTriggered('App\MyEvent');
   * $I->seeEventTriggered(new App\Events\MyEvent());
   * $I->seeEventTriggered(['App\MyEvent', 'App\MyOtherEvent']);
   * $I->seeEventTriggered('my_event_string_name');
   * $I->seeEventTriggered(['my_event_string_name', 'my_other_event_string]);
   * ```
   *
   * @param string|object|string[] $expected
   */
  public function seeEventTriggered($expected): void {
    $eventCollector = $this->grabEventCollector();

    $data = $eventCollector->getCalledListeners();
    $expected = is_array($expected) ? $expected : [$expected];

    $this->assertEventTriggered($data, $expected);
  }

  /**
   *
   */
  protected function assertEventNotTriggered(array $data, array $expected): void {
    foreach ($expected as $expectedEvent) {
      $expectedEvent = is_object($expectedEvent) ? get_class($expectedEvent) : $expectedEvent;
      $this->assertFalse(
            $this->eventWasTriggered($data, (string) $expectedEvent),
            "The '{$expectedEvent}' event triggered"
        );
    }
  }

  /**
   *
   */
  protected function assertEventTriggered(array $data, array $expected): void {
    if (count($data) === 0) {
      $this->fail('No event was triggered');
    }

    foreach ($expected as $expectedEvent) {
      $expectedEvent = is_object($expectedEvent) ? get_class($expectedEvent) : $expectedEvent;
      $this->assertTrue(
            $this->eventWasTriggered($data, (string) $expectedEvent),
            "The '{$expectedEvent}' event did not trigger"
        );
    }
  }

  /**
   *
   */
  protected function eventWasTriggered(array $actual, string $expectedEvent): bool {
    $triggered = FALSE;

    foreach ($actual as $name => $actualEvent) {
      // Called Listeners.
      if ($name === $expectedEvent && !empty($actualEvent)) {
        $triggered = TRUE;
      }
    }

    return $triggered;
  }

  /**
   * Get the event data collector service.
   */
  protected function grabEventCollector(): EventsDataCollector {
    $event_dispatcher = \Drupal::service('event_dispatcher');
    if ($event_dispatcher instanceof EventDispatcherTraceableInterface) {
      $collector = new EventsDataCollector($event_dispatcher);
      $collector->lateCollect();
      return $collector;
    }
    else {
      throw new \Exception('Webprofiler module is required for testing events.');
    }
  }

}
