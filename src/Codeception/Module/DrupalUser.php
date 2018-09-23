<?php

namespace Codeception\Module;

use Codeception\Module;
use Drupal\user\Entity\User;
use Faker\Factory;

/**
 * Class DrupalUser.
 */
class DrupalUser extends Module {

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
