<?php

namespace Ease\Html;

/**
 * Zobrazí input text tag.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class InputTextTag extends InputTag
{

    /**
     * Zobrazí input text tag.
     *
     * @param string $name       jméno
     * @param string $value      předvolená hodnota
     * @param array  $properties dodatečné vlastnosti tagu
     */
    public function __construct($name, $value = null, $properties = [])
    {
        if (!isset($properties['type'])) {
            $properties['type'] = 'text';
        }
        if (!is_null($value)) {
            $properties['value'] = $value;
        }
        if ($name) {
            $properties['name'] = $name;
        }
        $this->setTagProperties($properties);
        parent::__construct($name, $value);
    }
}
