<?php

namespace Codeception\Util;

/**
 * Class FormField provides form field path detection services.
 *
 * @package Page\Acceptance
 */
abstract class XpathBuilder {

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
  public $pattern;

  /**
   * Replacement key and values.
   *
   * @var array
   */
  protected $replacements;

  /**
   * AttributeXpathBuilder constructor.
   *
   * @param string $pattern
   *   Xpath pattern.
   * @param array $replacements
   *   Array of replacement values keyed by replacement key.
   */
  public function __construct($pattern, array $replacements) {
    $this->$pattern = $pattern;
    foreach ($replacements as $key => $value) {
      $value = is_int($value) ? strval($value) : $value;
      if (is_string($value)) {
        $this->setReplacement($key, $value);
      }
    }
  }

  /**
   * Initiates object of extending class.
   *
   * @param string $name
   *   Field name.
   * @param array $arguments
   *   Arguments like parent and position.
   *
   * @return \Page\Acceptance\XpathBuilder
   *   Returns initiated object.
   */
  public static function __callStatic($name, array $arguments) {
    $parent = NULL;
    $position = 0;
    foreach ($arguments as $argument) {
      if ($argument instanceof IdentifiableFormFieldInterface) {
        $parent = $argument;
      }
      if (is_int($argument)) {
        $position = $argument;
      }
    }
    return new static($name, $parent, $position);
  }

  /**
   * Sets replacement variables on to object.
   *
   * @param string $key
   *   Replaceable string.
   * @param string $value
   *   Replacement value.
   *
   * @return \Page\Acceptance\XpathBuilder
   *   Returns self.
   */
  public function setReplacement($key, $value) {
    $this->replacements[$key] = $value;
    return $this;
  }

  /**
   * Returns xpath.
   *
   * @param array $replacements
   *   Replacement data.
   *
   * @return string
   *   Xpath.
   */
  public function getXpath(array $replacements = []) {
    $replace = array_merge($this->replacements, $replacements);
    foreach ($replace as $key => $value) {
      $replace['{' . $key . '}'] = $value;
      unset($replace[$key]);
    }
    return str_replace(array_keys($replace), array_values($replace), $this->pattern);
  }

}
