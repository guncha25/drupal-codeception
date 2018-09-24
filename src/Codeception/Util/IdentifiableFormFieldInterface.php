<?php

namespace Codeception\Util;

/**
 * Interface IdentifiableFormFieldInterface.
 *
 * @package Codeception\Util
 */
interface IdentifiableFormFieldInterface {

  /**
   * Returns path identifier.
   *
   * @return string
   *   Path identifier string.
   */
  public function getIdentifier();

  /**
   * Returns xpath current identifier.
   *
   * @return string
   *   Current path identifier string.
   */
  public function getCurrentIdentifier();

}
