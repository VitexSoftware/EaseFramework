<?php

namespace Ease\Html;

/**
 * Zobrazí tag pro chcekbox.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class CheckboxTag extends InputTag
{

    /**
     * Zobrazuje HTML Checkbox.
     *
     * @param string $name       jméno tagu
     * @param bool   $checked    stav checkboxu
     * @param string $value      vracená hodnota checkboxu
     * @param array  $properties parametry tagu
     */
    public function __construct($name, $checked = false, $value = null,
                                $properties = [])
    {
        $properties['type'] = 'checkbox';
        if ($checked === true) {
            $properties['checked'] = 'true';
        }
        if (!is_null($value)) {
            $properties['value'] = $value;
        }
        if (strlen($name)) {
            $properties['name'] = $name;
        }
        $this->setTagProperties($properties);
        parent::__construct($name);
    }

    /**
     * Nastaví zaškrtnutí.
     *
     * @param bool $value nastavuje parametr "checked" tagu
     */
    public function setValue($value = true)
    {
        if (boolval($value)) {
            $this->setTagProperties(['checked' => 'true']);
        } else {
            unset($this->tagProperties['checked']);
        }
    }

    /**
     * Obtain curent checkbox state
     *
     * @return boolean $value
     */
    public function getValue()
    {
        return !empty($this->getTagProperty('checked'));
    }
}
