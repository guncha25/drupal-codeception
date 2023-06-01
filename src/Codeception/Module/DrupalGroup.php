<?php

namespace Codeception\Module;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Class DrupalGroup.
 *
 * ### Example
 * #### Example (DrupalGroup)
 *     modules:
 *        - DrupalGroup:
 *          cleanup_test: true
 *          cleanup_failed: false
 *          cleanup_suite: true
 *          route_entities:
 *            - node
 *            - taxonomy_term.
 *
 * @package Codeception\Module
 */
class DrupalGroup extends DrupalEntity {

  /**
   * Wrapper method to create a group.
   *
   * Improves readbility of tests by having the method read "create group".
   */
  public function createGroup(array $values = [], $validate = TRUE) {
    $type = 'group';

    if (!array_key_exists('uid', $values)) {
      $values['uid'] = 1;
    }

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
      // Group specific entity save options.
      $entity->setOwner(User::load($values['uid'] ?? 1));
      $entity->save();
    }
    catch (\Exception $e) {
      $this->fail('Could not create group entity. Error message: ' . $e->getMessage());
    }
    if (!empty($entity)) {
      $this->registerTestEntity($entity->getEntityTypeId(), $entity->id());

      return $entity;
    }

    return FALSE;
  }

  /**
   * Join the defined group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Instance of a group.
   * @param \Drupal\user\UserInterface $user
   *   A drupal user to add to the group.
   *
   * @return \Drupal\group\GroupMembership|false
   *   Returns the group content entity, FALSE otherwise.
   */
  public function joinGroup(GroupInterface $group, UserInterface $user) {
    $group->addMember($user);
    return $group->getMember($user);
  }

  /**
   * Leave a group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Instance of a group.
   * @param \Drupal\user\UserInterface $user
   *   A drupal user to add to the group.
   *
   * @return bool
   *   Returns the TRUE if the user is no longer a member of the group,
   *   FALSE otherwise.
   */
  public function leaveGroup(GroupInterface $group, UserInterface $user) {
    $group->removeMember($user);
    // Get member should return FALSE if the user isn't a member so we
    // reverse the logic. If they are still a member it'll cast to TRUE.
    $is_member = (bool) $group->getMember($user);
    return !$is_member;
  }

}
