<?php

namespace Codeception\Module;

use Codeception\Module;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Url;

/**
 * Class DrupalEntity.
 *
 * @package Codeception\Module
 */
class DrupalEntity extends Module {

  /**
   * Default module configuration.
   *
   * @var array
   */
  protected $config = [
    'cleanup_after_test' => TRUE,
    'cleanup_after_suite' => TRUE,
  ];

  /**
   * Entities to be deleted after test suite.
   *
   * @var array
   */
  protected $entities = [];

  /**
   * Executes after suite.
   *
   * {@inheritdoc}
   */
  public function _afterSuite() { // @codingStandardsIgnoreLine
    if ($this->config['cleanup_after_suite']) {
      $this->entityCleanup();
    }
  }

  /**
   * Executes after suite.
   *
   * {@inheritdoc}
   */
  public function _after(\Codeception\TestCase $test) { // @codingStandardsIgnoreLine
    if ($this->config['cleanup_after_test']) {
      $this->entityCleanup();
    }
  }

  /**
   * Executes after suite.
   *
   * {@inheritdoc}
   */
  public function _failed(\Codeception\TestCase $test) { // @codingStandardsIgnoreLine
    $this->_after($test);
  }

  /**
   * Create entity from values.
   *
   * @param array $values
   *   Data for creating entity.
   * @param string $type
   *   Entity type.
   *
   * @return \Drupal\Core\Entity\EntityInterface|bool
   *   Created entity.
   */
  public function createEntity(array $values = [], $type = 'node') {
    try {
      $entity = \Drupal::entityTypeManager()
        ->getStorage($type)
        ->create($values);
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
  public function entityCleanup() {
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
   * Get entity by name.
   *
   * @param string $name
   *   Name property of entity.
   * @param string $type
   *   Entity type.
   *
   * @return \Drupal\Core\Entity\EntityInterface|bool
   *   Media entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getEntityByName($name, $type = 'node') {
    $entities = \Drupal::entityTypeManager()
      ->getStorage($type)
      ->loadByProperties(['name' => $name]);

    if (!empty($entities)) {
      return end($entities);
    }

    return FALSE;
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
        if (isset($options['type']) && strpos($options['type'], 'entity:') === 0) {
          $entity = $url->getParameter($name);
          if ($entity instanceof ContentEntityInterface && $entity->hasLinkTemplate('canonical')) {
            return $entity;
          }

          // Since entity was found, no need to iterate further.
          return FALSE;
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
