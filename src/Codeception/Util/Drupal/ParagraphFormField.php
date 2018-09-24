<?php

namespace Codeception\Util\Drupal;

/**
 * Class ParagraphFormField.
 *
 * @package Codeception\Util\Drupal
 */
class ParagraphFormField extends FormField {

  /**
   * Returns current identifier of field.
   *
   * {@inheritdoc}
   */
  public function getCurrentIdentifier() {
    return $this->getIdentifier() . '-' . $this->position . '-subform';
  }

  /**
   * Returns xpath of add more button.
   *
   * @param string $type
   *   Type of paragraph to add..
   *
   * @return string
   *   Xpath of add more button.
   */
  public function addMore($type = '') {
    $button_suffix = empty($type) ? 'add-more-add-more-button' : 'add-more-add-more-button-' . $this->normalise($type);
    return $this->get($button_suffix);
  }

}
