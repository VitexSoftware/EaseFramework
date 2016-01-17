<?php

namespace Ease\Html;

/**
 * Odesílací tlačítko
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class SubmitButton extends InputTag
{

    /**
     * Popisek odesílacího tlačítka
     * @var string
     */
    public $label = null;

    /**
     * Odesílací tlačítko
     * <input type="submit" name="$label" value="$value" title="$Hint">
     *
     * @param string $label    nápis na tlačítku
     * @param string $value    odesílaná hodnota
     * @param string $Hint     tip při najetí myší
     * @param string $classCss css třída pro tag tlačítka
     */
    public function __construct($label, $value = null, $Hint = null, $classCss = null)
    {
        $properties = array('type' => 'submit');
        if (!$value) {
            $value = trim(str_replace(array(' ', '?'), '', @iconv('utf-8', 'us-ascii//TRANSLIT', strtolower($label))));
        } else {
            $properties['value'] = $value;
        }
        if ($Hint) {
            $properties['title'] = $Hint;
        }
        if ($classCss) {
            $properties['class'] = $classCss;
        }
        $this->setTagProperties($properties);
        parent::__construct($value, $label);
        $this->label = $label;
    }

    /**
     * Nastaví hodnotu
     *
     * @param string  $value     vracená hodnota tagu
     * @param boolean $Automatic Hack pro zachování labelů při plnění formuláře
     */
    public function setValue($value, $Automatic = false)
    {
        if (!$Automatic) {
            //FillUp nenastavuje Labely tlačítek
            parent::SetValue($value);
        }
    }

}
