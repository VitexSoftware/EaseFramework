<?php

namespace Ease\Html;

/**
 * Send button
 * Odesílací tlačítko.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class SubmitButton extends InputTag
{
    /**
     * Popisek odesílacího tlačítka.
     *
     * @var string
     */
    public $label = null;

    /**
     * Odesílací tlačítko
     * <input type="submit" name="$label" value="$value" title="$Hint">.
     *
     * @param string $label    nápis na tlačítku
     * @param string $value    odesílaná hodnota
     * @param string $hint     tip při najetí myší
     * @param string $classCss css třída pro tag tlačítka
     */
    public function __construct($label, $value = null, $hint = null,
                                $classCss = null)
    {
        $properties = ['type' => 'submit'];
        if (is_null($value)) {
            $value = trim(str_replace([' ', '?'], '',
                    @iconv('utf-8', 'us-ascii//TRANSLIT', strtolower($label))));
        } else {
            $properties['value'] = $value;
        }
        if (!empty($hint)) {
            $properties['title'] = $hint;
        }
        if (!is_null($classCss)) {
            $properties['class'] = $classCss;
        }
        $this->setTagProperties($properties);
        parent::__construct($value, $label);
        $this->label = $label;
    }

    /**
     * Nastaví hodnotu.
     *
     * @param string $value     vracená hodnota tagu
     * @param bool   $Automatic Hack pro zachování labelů při plnění formuláře
     */
    public function setValue($value, $Automatic = false)
    {
        if (!$Automatic) {
            //FillUp nenastavuje Labely tlačítek
            parent::SetValue($value);
        }
    }
}
