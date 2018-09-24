<?php

namespace Codeception\Module;

use Codeception\Module;
use Drupal\user\Entity\User;
use Faker\Factory;
use Codeception\Util\Drush;

/**
 * Class DrupalUser.
 *
 * @package Codeception\Module
 */
class DrupalUser extends Module {

  /**
   * Driver to use.
   *
   * @var WebDriver|PhpBrowser
   */
  protected $driver;

  /**
   * A list of user ids created during test suite.
   *
   * @var array
   */
  protected $users;

  /**
   * Default module configuration.
   *
   * @var array
   */
  protected $config = [
    'default_role' => 'authenticated',
    'driver' => 'WebDriver',
    'drush' => 'drush',
    'cleanup_entities' => [],
    'cleanup_test' => TRUE,
    'cleanup_failed' => TRUE,
    'cleanup_suite' => TRUE,
  ];

  /**
   * Create test user with specified roles.
   *
   * @param array $roles
   *   List of user roles.
   * @param mixed $password
   *   Password.
   *
   * @return \Drupal\user\Entity\User
   *   User object.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createUserWithRoles(array $roles = [], $password = FALSE) {
    $faker = Factory::create();
    /** @var \Drupal\user\Entity\User $user */
    $user = \Drupal::entityTypeManager()->getStorage('user')->create([
      'name' => $faker->userName,
      'mail' => $faker->email,
      'roles' => empty($roles) ? $this->config['default_role'] : $roles,
      'pass' => $password ? $password : $faker->password(12, 14),
      'status' => 1,
    ]);

    $user->save();
    $this->users[] = $user->id();

    return $user;
  }

  /**
   * Log in user by username.
   *
   * @param string|int $username
   *   User id.
   */
  public function logInAs($username) {
    $output = Drush::runDrush('uli --name=' . $username, $this->config['drush'], $this->_getConfig('working_directory'));
    $gen_url = str_replace(PHP_EOL, '', $output);
    $url = substr($gen_url, strpos($gen_url, '/user/reset'));
    $this->driver->amOnPage($url);
  }

  /**
   * Create user with role and Log in.
   *
   * @param string $role
   *   Role.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function logInWithRole($role) {
    $user = $this->createUserWithRoles([$role], Factory::create()->password(12, 14));

    $this->logInAs($user->getUsername());
  }

  /**
   * {@inheritdoc}
   */
  public function _beforeSuite($settings = []) { // @codingStandardsIgnoreLine
    $this->driver = null;
    if (!$this->hasModule($this->_getConfig('driver'))) {
      $this->fail('User driver module not found.');
    }
    $this->driver = $this->getModule($this->config['driver']);
  }

  /**
   * {@inheritdoc}
   */
  public function _after(\Codeception\TestCase $test) { // @codingStandardsIgnoreLine
    if ($this->config['cleanup_test']) {
      $this->userCleanup();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function _failed(\Codeception\TestCase $test, $fail) { // @codingStandardsIgnoreLine
    if ($this->config['cleanup_failed']) {
      $this->userCleanup();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function _afterSuite() { // @codingStandardsIgnoreLine
    if ($this->config['cleanup_suite']) {
      $this->userCleanup();
    }
  }

  /**
   * Delete stored entities.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function userCleanup() {
    if (isset($this->users)) {
      $users = User::loadMultiple($this->users);
      /** @var \Drupal\user\Entity\User $user */
      foreach ($users as $user) {
        $this->deleteUsersContent($user->id());
        $user->delete();
      }
    }
  }

  /**
   * Delete user created entities.
   *
   * @param string|int $uid
   *   User id.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function deleteUsersContent($uid) {
    $cleanup_entities = $this->_getConfig('cleanup_entities');
    if (is_array($cleanup_entities)) {
      foreach ($cleanup_entities as $cleanup_entity) {
        try {
          $storage = \Drupal::entityTypeManager()->getStorage($cleanup_entity);
        }
        catch (\Exception $e) {
          continue;
        }
        $entities = $storage->loadByProperties(['uid' => $uid]);
        foreach ($entities as $entity) {
          $entity->delete();
        }
      }
    }
  }

}
