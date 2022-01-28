<?php

namespace Codeception\Module;

use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Util\Drupal\FormField;
use Codeception\Util\Drupal\ParagraphFormField;
use Codeception\Util\IdentifiableFormFieldInterface;
use Facebook\WebDriver\Remote\RemoteWebDriver;

/**
 * Class DrupalAcceptance.
 *
 * ### Example
 * #### Example (DrupalAcceptance)
 *     modules:
 *        - DrupalAcceptance.
 *
 * @package Codeception\Module
 */
class DrupalAcceptance extends Module {

  /**
   * Web-driver.
   *
   * @var \Codeception\Module
   */
  protected $webdriver;

  /**
   * DrupalAcceptance constructor.
   *
   * @param \Codeception\Lib\ModuleContainer $moduleContainer
   *   Module container.
   * @param null|mixed $config
   *   Configurations.
   *
   * @throws \Codeception\Exception\ModuleException
   */
  public function __construct(ModuleContainer $moduleContainer, $config = NULL) {
    parent::__construct($moduleContainer, $config);
    $this->webdriver = $this->getModule("WebDriver");
  }

  /**
   * Add paragraph element.
   *
   * @param string $type
   *   Paragraph field.
   * @param \Codeception\Util\Drupal\ParagraphFormField $field
   *   Paragraph field.
   */
  public function addParagraph($type, ParagraphFormField $field) {
    $this->webdriver->click($field->addMore($type));
    $this->webdriver->waitForElementClickable($field->getCurrent('subform'));
  }

  /**
   * Remove paragraph element at current position.
   *
   * @param \Codeception\Util\Drupal\ParagraphFormField $paragraph
   *   Paragraph field.
   */
  public function removeParagraph(ParagraphFormField $paragraph) {
    $this->webdriver->click($paragraph->get($paragraph->position . ' top links remove button'));
    $this->webdriver->waitForElementClickable($paragraph->get($paragraph->position . ' top links confirm remove button'));
    $this->webdriver->click($paragraph->get($paragraph->position . ' top links confirm remove button'));
    $this->webdriver->waitForElementNotVisible($paragraph->getCurrent('Subform'));
  }

  /**
   * Fill text field.
   *
   * @param \Codeception\Util\IdentifiableFormFieldInterface $field
   *   Text form field.
   * @param string $value
   *   Value.
   */
  public function fillTextField(IdentifiableFormFieldInterface $field, $value) {
    $this->webdriver->fillField($field->value, $value);
  }

  /**
   * Fill link field.
   *
   * @param \Codeception\Util\IdentifiableFormFieldInterface $field
   *   Text form field.
   * @param string $uri
   *   Uri.
   * @param string $title
   *   Title.
   */
  public function fillLinkField(IdentifiableFormFieldInterface $field, $uri, $title = NULL) {
    $this->webdriver->fillField($field->uri, $uri);
    if (isset($title)) {
      $this->webdriver->fillField($field->title, $title);
    }
  }

  /**
   * Fill reference autocomplete field.
   *
   * @param \Codeception\Util\IdentifiableFormFieldInterface $field
   *   Reference form field.
   * @param string $target_label
   *   Target reference label.
   */
  public function fillReferenceField(IdentifiableFormFieldInterface $field, $target_label) {
    $this->webdriver->fillField($field->target_id, $target_label);
  }

  /**
   * Select option from select list.
   *
   * @param \Codeception\Util\IdentifiableFormFieldInterface $field
   *   Select list form field.
   * @param string $option
   *   Option.
   * @param string $target
   *   Target field.
   */
  public function selectOptionFromList(IdentifiableFormFieldInterface $field, $option, $target = 'value') {
    $this->webdriver->selectOption($field->$target, $option);
  }

  /**
   * Click on element.
   *
   * @param \Codeception\Util\IdentifiableFormFieldInterface $field
   *   Select list form field.
   * @param string $target
   *   Target field.
   */
  public function clickOn(IdentifiableFormFieldInterface $field, $target = '') {
    $this->webdriver->click($field->get($target));
  }

  /**
   * Adds next reference item for field.
   *
   * @param \Codeception\Util\Drupal\FormField $field
   *   Select list form field.
   */
  public function addReferenceFieldItem(FormField $field) {
    $this->webdriver->click($field->addMore());
    $this->webdriver->waitForElementClickable($field->target_id);
  }

  /**
   * Fill WYSIWYG editor.
   *
   * @param \Codeception\Util\IdentifiableFormFieldInterface $field
   *   Element xpath.
   * @param string $content
   *   Text to insert in CkEditor.
   */
  public function fillWysiwygEditor(IdentifiableFormFieldInterface $field, $content) {
    $selector = $this->webdriver->grabAttributeFrom($field->value, 'id');
    $script = "jQuery(function(){CKEDITOR.instances[\"$selector\"].setData(\"$content\")});";
    $this->webdriver->executeInSelenium(function (RemoteWebDriver $webDriver) use ($script) {
      $webDriver->executeScript($script);
    });
    $this->webdriver->wait(1);
  }

  /**
   * Get js logs.
   *
   * @return mixed
   *   String
   *
   * @throws \Codeception\Exception\ModuleException
   */
  public function getJsLog() {
    $wb = $this->getModule("WebDriver");
    $logs = $wb->webDriver->manage()->getLog("browser");
    return $logs;
  }

}
