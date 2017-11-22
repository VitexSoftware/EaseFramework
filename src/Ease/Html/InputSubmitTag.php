<?php

namespace Ease\Html;

/**
 * Odesílací tlačítko formuláře.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class InputSubmitTag extends InputTag
{

    /**
     * Odesílací tlačítko formuláře.
     *
     * @param string $name       jméno tagu
     * @param string $value      vracená hodnota
     * @param array  $properties Pole vlastností tagu
     */
    public function __construct($name, $value = null, $properties = [])
    {
        if (is_null($value)) {
            $value = $name;
        }
        if (is_null($properties)) {
            $properties = [];
        }
        $properties['type']  = 'submit';
        $properties['name']  = $name;
        $properties['value'] = $value;
        parent::__construct($name, $value, $properties);
    }

    /**
     * Maketa kuli popisku.
     *
     * @param bool $value je ignorováno
     */
    public function setValue($value = true)
    {

    }
}
