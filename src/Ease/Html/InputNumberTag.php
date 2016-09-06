<?php

namespace Ease\Html;

/**
 * Vstupní pole čísla.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class InputNumberTag extends InputTag
{

    /**
     * Vstupní pole čísla.
     *
     * @param string $name       jméno
     * @param string $value      předvolená hodnota
     * @param array  $properties dodatečné vlastnosti tagu
     */
    public function __construct($name, $value = null, $properties = null)
    {
        $properties['type'] = 'number';
        if ($value) {
            $properties['value'] = $value;
        }
        if ($name) {
            $properties['name'] = $name;
        }
        $this->setTagProperties($properties);
        parent::__construct($name, $value);
    }
}