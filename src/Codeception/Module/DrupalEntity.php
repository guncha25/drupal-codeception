<?php

namespace Codeception\Module;

use Codeception\Module;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Url;
use Codeception\TestInterface;

/**
 * Class DrupalEntity.
 *
 * ### Example
 * #### Example (DrupalEntity)
 *     modules:
 *        - DrupalEntity:
 *          cleanup_test: true
 *          cleanup_failed: false
 *          cleanup_suite: true
 *          route_entities:
 *            - node
 *            - taxonomy_term.
 *
 * @package Codeception\Module
 */
class DrupalEntity extends Module {

  /**
   * Default module configuration.
   *
   * @var array
   */
  protected array $config = [
    'cleanup_test' => TRUE,
    'cleanup_failed' => TRUE,
    'cleanup_suite' => TRUE,
    'route_entities' => [
      'node',
      'taxonomy_term',
      'media',
    ],
  ];

  /**
   * Entities to be deleted after test suite.
   *
   * @var array
   */
  protected $entities = [];

  /**
   * {@inheritdoc}
   */
  public function _afterSuite() { // @codingStandardsIgnoreLine
    if ($this->config['cleanup_suite']) {
      $this->doEntityCleanup();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function _after(TestInterface $test) { // @codingStandardsIgnoreLine
    if ($this->config['cleanup_test']) {
      $this->doEntityCleanup();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function _failed(TestInterface $test, $fail) { // @codingStandardsIgnoreLine
    if ($this->config['cleanup_failed']) {
      $this->doEntityCleanup();
    }
  }

  /**
   * Create entity from values.
   *
   * @param array $values
   *   Data for creating entity.
   * @param string $type
   *   Entity type.
   * @param bool $validate
   *   Flag to validate entity fields..
   *
   * @return \Drupal\Core\Entity\EntityInterface|bool
   *   Created entity.
   */
  public function createEntity(array $values = [], $type = 'node', $validate = TRUE) {
    try {
      $entity = \Drupal::entityTypeManager()
        ->getStorage($type)
        ->create($values);
      if ($validate && $entity instanceof FieldableEntityInterface) {
        $violations = $entity->validate();
        if ($violations->count() > 0) {
          $message = PHP_EOL;
          foreach ($violations as $violation) {
            $message .= $violation->getPropertyPath() . ': ' . $violation->getMessage() . PHP_EOL;
          }
          throw new \Exception($message);
        }
      }

      $entity->save();
    }
    catch (\Exception $e) {
      $this->fail('Could not create entity. Error message: ' . $e->getMessage());
    }
    if (!empty($entity)) {
      $this->registerTestEntity($entity->getEntityTypeId(), $entity->id());

      return $entity;
    }

    return FALSE;
  }

  /**
   * Delete stored entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function doEntityCleanup() {
    foreach ($this->entities as $type => $ids) {
      $entities = \Drupal::entityTypeManager()
        ->getStorage($type)
        ->loadMultiple($ids);
      foreach ($entities as $entity) {
        $entity->delete();
      }
    }
  }

  /**
   * Register test entity to be deleted after tests.
   *
   * @param string $type
   *   Entity type.
   * @param string|int $id
   *   Entity id.
   */
  public function registerTestEntity($type, $id) {
    try {
      \Drupal::entityTypeManager()->getStorage($type);
    }
    catch (\Exception $e) {
      $this->fail('Invalid entity type specified: ' . $type);
    }
    $this->entities[$type][] = $id;
  }

  /**
   * Gets entity form route.
   *
   * @param string $url
   *   Uri.
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface
   *   Entity or FALSE.
   */
  public function getEntityFromUrl($url) {
    $url = Url::fromUri("internal:" . $url);
    if ($parameters = $url->getRouteParameters()) {
      // Determine if the current route represents an entity.
      foreach ($parameters as $name => $options) {
        $entity = $url->getRouteParameters();
        if (in_array($name, $this->_getConfig('route_entities'))) {
          try {
            $storage = \Drupal::entityTypeManager()->getStorage($name);
            $entity = $storage->load($options);
            if ($entity) {
              return $entity;
            }
          }
          catch (\Exception $e) {
            continue;
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * Register test entity by url.
   *
   * @param string $url
   *   Url.
   */
  public function registerTestEntityByUrl($url) {
    if ($entity = $this->getEntityFromUrl($url)) {
      $this->registerTestEntity($entity->getEntityTypeId(), $entity->id());
    }
  }

}
