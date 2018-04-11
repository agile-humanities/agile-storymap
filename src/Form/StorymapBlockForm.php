<?php

namespace Storymap\Form;

use Omeka\Form\Element\PropertySelect;
use Storymap\Mvc\Controller\Plugin\StorymapData;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\Fieldset;
use Zend\Form\Form;

class StorymapBlockForm extends Form {

  public function init() {
    $this->add([
      'name' => 'o:block[__blockIndex__][o:data][args]',
      'type' => Fieldset::class,
      'options' => [
        'label' => 'Parameters', // @translate
      ],
    ]);
    $argsFieldset = $this->get('o:block[__blockIndex__][o:data][args]');


    $argsFieldset->add([
      'name' => 'item_title',
      'type' => PropertySelect::class,
      'options' => [
        'label' => 'Item title',
        // @translate
        'info' => 'The title field to use when displaying an item on a storymap. Default is "dcterms:title".',
        // @translate
        'empty_option' => 'Select a property...',
        // @translate
        'term_as_value' => TRUE,
      ],
      'attributes' => [
        'class' => 'chosen-select',
        'required' => TRUE,
      ],
    ]);

    $argsFieldset->add([
      'name' => 'item_description',
      'type' => PropertySelect::class,
      'options' => [
        'label' => 'Item description',
        // @translate
        'info' => 'The description field to use when displaying an item on a storymap. Default is "dcterms:description".',
        // @translate
        'empty_option' => 'Select a property...',
        // @translate
        'term_as_value' => TRUE,
      ],
      'attributes' => [
        'class' => 'chosen-select',
        'required' => TRUE,
      ],
    ]);

    $argsFieldset->add([
      'name' => 'item_date',
      'type' => PropertySelect::class,
      'options' => [
        'label' => 'Item date',
        // @translate
        'info' => "The date field to use to retrieve and display items on a storymap. Default is \"dcterms:date\"",
        // @translate
        'empty_option' => 'Select a property...',
        // @translate
        'term_as_value' => TRUE,
      ],
      'attributes' => [
        'class' => 'chosen-select',
        'required' => TRUE,
      ],
    ]);
    $argsFieldset->add([
      'name' => 'item_date',
      'type' => PropertySelect::class,
      'options' => [
        'label' => 'Item date',
        // @translate
        'info' =>'The date field to use to retrieve and display items on a storymap. Default is "dcterms:date".',
        // @translate
        'empty_option' => 'Select a property...',
        // @translate
        'term_as_value' => TRUE,
      ],
      'attributes' => [
        'class' => 'chosen-select',
        'required' => TRUE,
      ],
    ]);
    $argsFieldset->add([
      'name' => 'item_location',
      'type' => PropertySelect::class,
      'options' => [
        'info' => 'Latitude and longitude, as comma separated values',
        'label' => 'Item location', // @translate
        'empty_option' => 'Select a property...', // @translate
        'term_as_value' => TRUE,
      ],
      'attributes' => [
        'required' => TRUE,
        'class' => 'chosen-select',
      ],
    ]);
    $argsFieldset->add([
      'name' => 'item_type',
      'type' => PropertySelect::class,
      'options' => [
        'info' => "Slide type - must be either blank, or 'overview'",
        'label' => 'Item type', // @translate
        'empty_option' => 'Select a property...', // @translate
        'term_as_value' => TRUE,
      ],
      'attributes' => [
        'required' => FALSE,
        'class' => 'chosen-select',
      ],
    ]);
    $argsFieldset->add([
      'name' => 'item_contributor',
      'type' => PropertySelect::class,
      'options' => [
        'label' => 'Media credit', // @translate
        'empty_option' => 'Select a property...', // @translate
        'term_as_value' => TRUE,
      ],
      'attributes' => [
        'required' => FALSE,
        'class' => 'chosen-select',
      ],
    ]);
    $argsFieldset->add([
      'name' => 'viewer',
      'type' => 'Textarea',
      'options' => [
        'label' => 'Viewer',
        'info' => 'Set the default params of the viewer as json, or let empty for the included default.',

      ],
      'attributes' => [
        'rows' => 15,
      ],
    ]);
    $inputFilter = $this->getInputFilter();
    $inputFilter->add([
      'name' => 'o:block[__blockIndex__][o:data][args]',
      'required' => FALSE,
    ]);
  }

}
