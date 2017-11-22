<?php

namespace Ease\Html;

/**
 * Skrytý input.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class InputHiddenTag extends InputTag
{

    /**
     * Skrytý input.
     *
     * @param string $name       jméno tagu
     * @param string $value      vracená hodnota
     * @param array  $properties vlastnosti tagu
     */
    public function __construct($name, $value = null, $properties = [])
    {
        parent::__construct($name, $value);
        $properties['type'] = 'hidden';
        $this->setTagProperties($properties);
    }
}
