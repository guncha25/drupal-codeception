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
   */
  public function createUserWithRoles(array $roles = [], $password = FALSE) {
    $faker = Factory::create();
    /** @var \Drupal\user\Entity\User $user */
    $user = User::create([
      'name' => $faker->userName,
      'mail' => $faker->email,
      'roles' => empty($roles) ? $this->config['default_role'] : $roles,
      'pass' => $password ? $password : $faker->password(12, 14),
      'status' => 1,
    ]);

    try {
      $user->save();
    }
    catch (\Exception $e) {
      $this->fail($e);
    }
    $this->users[] = $user->id();

    return $user;
  }

  /**
   * Log in user by id.
   *
   * @param string|int $uid
   *   User id.
   */
  public function logInWithUid($uid) {
    $output = Drush::runDrush('uli --uid=' . $uid, $this->config['drush'], $this->_getConfig('working_directory'));
    $gen_url = str_replace(PHP_EOL, '', $output);
    $url = substr($gen_url, strpos($gen_url, '/user/reset'));
    $this->driver->amOnPage($url);
  }

  /**
   * Create user with role and Log in.
   *
   * @param string $role
   *   Role.
   */
  public function logInWithRole($role) {
    $user = $this->createUserWithRoles([$role]);

    $this->logInWithRole($user->id());
  }

  /**
   * {@inheritdoc}
   */
  public function _beforeSuite($settings = []) { // @codingStandardsIgnoreLine
    $this->driver = null;
    if (!$this->hasModule($this->config['module'])) {
      $this->fail('User driver module not found.');
    }
    $this->driver = $this->getModule($this->config['driver']);
  }

  /**
   * {@inheritdoc}
   */
  public function _afterSuite() { // @codingStandardsIgnoreLine
    if (isset($this->users)) {
      $users = User::loadMultiple($this->users);
      /** @var \Drupal\user\Entity\User $user */
      foreach ($users as $user) {
        $user->delete();
      }
    }
  }

}
