<?php

namespace Codeception\Util\Drupal;

use Codeception\Util\XpathBuilder;
use Codeception\Util\IdentifiableFormFieldInterface;

/**
 * Class MTOFormField.
 *
 * @package Codeception\Util\Drupal
 */
class MTOFormField extends XpathBuilder implements IdentifiableFormFieldInterface {

  /**
   * String that identifies form element.
   *
   * @var string
   */
  protected $identifier;

  /**
   * Xpath pattern.
   *
   * @var string
   */
  public $pattern = "//{element}[@{attribute}=\"{identifier}\"]";

  /**
   * Parent of form field.
   *
   * @var \Codeception\Util\IdentifiableFormFieldInterface|null
   */
  public $parent;

  /**
   * Field name.
   *
   * @var string
   */
  public $fieldName;

  /**
   * Replacement variables for xpath.
   *
   * @var array
   */
  protected $replacements = [
    'element' => '*',
    'attribute' => 'data-drupal-selector',
    'identifier' => '',
  ];

  /**
   * FormField constructor.
   *
   * @param string $fieldName
   *   Field name.
   * @param null|IdentifiableFormFieldInterface $parent
   *   Parent of form field.
   *
   * @throws \Exception
   */
  public function __construct($fieldName, $parent = NULL) {
    parent::__construct($this->pattern, $this->replacements);
    $this->fieldName = $fieldName;
    $this->parent = $parent;
    $this->setIdentifier();
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $name = isset($this->parent) ? strval($this->parent) . ' > ' : '';
    $name .= ucfirst(str_replace('_', ' ', preg_replace('@^field_@', '', $this->fieldName)));
    return $name;
  }

  /**
   * Returns xpath of current identifiers element.
   *
   * @param string $element
   *   Name of element.
   *
   * @return string
   *   Returns path with current identifier plus requested subfield.
   */
  public function __get($element = '') {
    return $this->get($element);
  }

  /**
   * Returns form field prefix.
   *
   * {@inheritdoc}
   */
  public function getIdentifier() {
    return $this->identifier;
  }

  /**
   * Sets identifier from current properties.
   *
   * @throws \Exception
   */
  public function setIdentifier() {
    $prefix = 'edit-';
    if ($this->parent instanceof IdentifiableFormFieldInterface) {
      $prefix = $this->parent->getCurrentIdentifier() . '-';
    }
    if (!is_string($this->fieldName)) {
      throw new \Exception('Provided field must be string.');
    }
    $this->identifier = $prefix . $this->normalise($this->fieldName);
  }

  /**
   * Returns form field current prefix with position.
   *
   * {@inheritdoc}
   */
  public function getCurrentIdentifier() {
    return $this->getIdentifier();
  }

  /**
   * Returns xpath of global identifiers element.
   *
   * @param string $element
   *   Name of element.
   *
   * @return string
   *   Returns path with identifier plus requested subfield.
   */
  public function get($element = '') {
    $suffix = $element ? '-' . $this->normalise($element) : '';
    return $this->getXpath([
      'identifier' => $this->getIdentifier() . $suffix,
    ]);
  }

  /**
   * Normalises string.
   *
   * @param string $string
   *   String to normalise.
   *
   * @return mixed
   *   Returns normalised string.
   */
  protected function normalise($string) {
    return str_replace([' ', '_'], '-', strtolower($string));
  }

}
