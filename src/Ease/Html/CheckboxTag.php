<?php

namespace Ease\Html;

/**
 * Zobrazí tag pro chcekbox
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class CheckboxTag extends InputTag
{

    /**
     * Zobrazuje HTML Checkbox
     *
     * @param string $name       jméno tagu
     * @param bool   $checked    stav checkboxu
     * @param string $value      vracená hodnota checkboxu
     * @param array  $properties parametry tagu
     */
    public function __construct($name, $checked = false, $value = null, $properties = null)
    {
        if ($properties) {
            $properties['type'] = 'checkbox';
        } else {
            $properties = ['type' => 'checkbox'];
        }
        if ($checked) {
            $properties['checked'] = 'true';
        }
        if ($value) {
            $properties['value'] = $value;
        }
        if ($name != '') {
            $properties['name'] = $name;
        }
        $this->setTagProperties($properties);
        parent::__construct($name);
    }

    /**
     * Nastaví zaškrtnutí
     *
     * @param boolean $value nastavuje parametr "checked" tagu
     */
    public function setValue($value = true)
    {
        if ($value) {
            $this->setTagProperties(['checked' => 'true']);
        } else {
            unset($this->tagProperties['checked']);
        }
    }

}
