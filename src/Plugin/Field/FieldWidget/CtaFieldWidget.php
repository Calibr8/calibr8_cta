<?php

/**
 * @file
 * Contains \Drupal\calibr8_cta\Plugin\Field\FieldWidget\CtaFieldWidget.
 */

namespace Drupal\calibr8_cta\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\link\Plugin\Field\FieldWidget\LinkWidget;
use Drupal\Component\Utility\Html;


/**
 * Plugin implementation of the 'segue_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "calibr8_cta_field_widget",
 *   label = @Translation("Calibr8 CTA"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class CtaFieldWidget extends LinkWidget {
  /**
   * {@inheritdoc}
   *
   * Adds an empty 'classes' default value.
   */
  public static function defaultSettings() {
    return array(
      'classes' => '',
      'id' => '',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   *
   * Set up the classes required.
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['classes'] = array(
      '#type' => 'textarea',
      '#rows' => '5',
      '#title' => t('Classes'),
      '#default_value' => $this->getSetting('classes'),
      '#description' => 'Enter options one per line, for example <em>button button-primary|Button (Primary)</em>',
    );
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $classes = $this->parseClassesSetting();
    $summary[] = "Classes: " . implode(', ', array_keys($classes));
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    /** @var \Drupal\link\LinkItemInterface $item */
    $item = $items[$delta];
    $options = $this->parseClassesSetting();

    // Classes
    if($options) {
      $element['classes'] = array(
        '#title' => t('Style'),
        '#type' => 'select',
        '#default_value' => !empty($item->options['attributes']['class']) ? $item->options['attributes']['class'] : '',
        '#options' => $options,
      );
    }

    // ID
    $random_id = uniqid('cta-');
    $element['id'] = array(
      '#title' => t('ID'),
      '#type' => 'textfield',
      '#default_value' => !empty($item->options['attributes']['id']) ? $item->options['attributes']['id'] : $random_id,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * Does as LinkWidget and adds classes, to be stored in the options array.
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      $value['uri'] = static::getUserEnteredStringAsUri($value['uri']);
      if(isset($value['classes'])) {
        $value['options']['attributes']['class'] = $value['classes'];
      }
      if($value['id'] != '') {
        $value['options']['attributes']['id'] = $value['id'];
      }
    }
    return $values;
  }

  /**
   * Parse classes setting.
   *
   * Classes are entered in a textarea like:
   *   button|Button
   *   button button-primary|Button (Primary)
   *
   * @return array
   */
  protected function parseClassesSetting() {
    $parsed = [];
    foreach (explode("\n", $this->getSetting('classes')) as $line) {
      $split = preg_split("/[|]/", $line, 2);
      if(isset($split[0]) && isset($split[1])) {
        $parsed[trim($split[0])] = Html::escape($split[1]);
      }
    }
    return $parsed;
  }

}
