<?php

namespace Ease\JQuery;

/**
 * Slider
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @see http://docs.jquery.com/UI/Slider
 */
class Slider extends UIPart
{

    /**
     * Class used to create form input
     * @var type
     */
    public $inputClass = 'Ease\Html\InputHiddenTag';

    /**
     * Additional JS code to solve show slider values
     * @var type
     */
    public $SliderAdd = '';

    /**
     * Jquery Slider
     *
     * @param string $name
     * @param int    $value can be array for multislider
     */
    public function __construct($name, $value = null)
    {
        $this->partName = $name;
        parent::__construct();
        if (!is_null($value)) {
            $this->setValue($value);
        }
    }

    /**
     * Nastavuje jméno objektu
     * Je li znnámý, doplní jméno objektu jménem inputu
     *
     * @param string $ObjectName vynucené jméno objektu
     *
     * @return string new name
     */
    public function setObjectName($ObjectName = null)
    {
        if ($ObjectName) {
            return parent::setObjectName($ObjectName);
        } else {
            if ($this->partName) {
                return parent::setObjectName(get_class($this) . '@' . $this->partName);
            } else {
                return parent::setObjectName();
            }
        }
    }

    /**
     * Setup input field/s value/s
     *
     * @param string $value
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->setPartProperties(array('values' => $value));
        } else {
            $this->setPartProperties(array('value' => $value));
        }
    }

    /**
     * Nastaví více hodnot
     *
     * @param darray $data hodnoty k přednastavení
     */
    public function setValues($data)
    {
        if (isset($this->partProperties['values'])) {
            $newValues = array();
            foreach (array_keys($this->partProperties['values']) as $Offset => $ID) {
                if (isset($data[$ID])) {
                    $this->pageParts[$this->inputClass . '@' . $ID]->setValue($data[$ID]);
                    $newValues[$ID] = $data[$ID];
                }
            }
            if (count($newValues)) {
                $this->setValue($newValues);
            }
        }
    }

    /**
     * Return assigned form input Tag name
     *
     * @return string
     */
    public function getTagName()
    {
        return $this->partName;
    }

    /**
     * Javascriptvový kod slideru
     *
     * @return string
     */
    public function onDocumentReady()
    {
        $javaScript = '$("#' . $this->partName . '-slider").slider( { ' . $this->getPartPropertiesToString() . ' } );';
        if (isset($this->partProperties['values'])) {
            foreach (array_keys($this->partProperties['values']) as $offset => $ID) {
                $javaScript .= '
' . '$( "#' . $ID . '" ).val( $( "#' . $this->partName . '-slider" ).slider( "values", ' . $offset . ' ) );';
            }
        } else {
            $javaScript .= '
' . '$( "#' . $this->partName . '" ).val( $( "#' . $this->partName . '-slider" ).slider( "value" ) );';
        }
        return $javaScript;
    }

    /**
     * Naplnění hodnotami
     */
    public function afterAdd()
    {
        if (isset($this->partProperties['values'])) {
            if (is_array($this->partProperties['values'])) {
                foreach ($this->partProperties['values'] as $valueID => $value) {
                    $this->addItem(new $this->inputClass($valueID, $value));
                    $this->lastItem->setTagID($valueID);
                }
            }
        } else {
            $this->addItem(new $this->inputClass($this->partName, $this->partProperties['value']));
            $this->lastItem->setTagID($this->partName);
        }
    }

    /**
     * Vložení skriptů do schránky
     */
    public function finalize()
    {
        \Ease\Shared::webPage()->addCSS(' #' . $this->partName . ' { margin: 10px; }');
        $this->addItem(new Ease\Html\DivTag($this->partName . '-slider'));
        if (isset($this->partProperties['values'])) {
            if (is_array($this->partProperties['values'])) {
                $JavaScript = '';
                foreach (array_keys($this->partProperties['values']) as $Offset => $ID) {
                    $JavaScript .= ' $( "#' . $ID . '" ).val( ui.values[' . $Offset . '] );';
                }
                $this->setPartProperties(array('slide' => 'function (event, ui) { ' . $JavaScript . $this->SliderAdd . ' }'));
            }
        } else {
            $this->setPartProperties(array('slide' => 'function (event, ui) { $( "#' . $this->partName . '" ).val( ui.value ); ' . $this->SliderAdd . ' }'));
        }
        if (!isset($this->partProperties['value'])) {
            $this->partProperties['value'] = null;
        }
        $this->setPartProperties(array('change' => 'function (event, ui) {
            $("#' . $this->partName . '-slider a").html( ui.value ); }', 'create' => 'function (event, ui) { $("#' . $this->partName . '-slider a").html( ' . $this->partProperties['value'] . ' ).css("text-align", "center"); }  '));
        \Ease\Shared::webPage()->addJavaScript(';', null, true);
        return parent::finalize();
    }

}
