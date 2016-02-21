<?php

namespace Ease\Html;

/**
 * Radio button
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class InputRadioTag extends InputTag
{

    /**
     * Vracená hodnota
     *
     * @var string
     */
    public $value = null;

    /**
     * Radio button
     *
     * @param string $name          jméno tagu
     * @param string $value         vracená hodnota
     * @param array  $tagProperties vlastnosti tagu
     */
    public function __construct($name, $value = null, $tagProperties = null)
    {
        parent::__construct($name, $value);
        if ($tagProperties) {
            $this->setTagProperties($tagProperties);
        }
        $this->setTagProperties(['type' => 'radio']);
        $this->Value = $value;
    }

    /**
     * Poprvé nastaví hodnotu checkboxu. Druhé volání nastavuje příznak checked,
     * pokud je hodnota stejná jako již nabitá
     *
     * @param string $value vracená hodnota
     *
     * @todo boolean $Automatic zabraňuje mazání textu z tlačítek v objektu SubmitButton
     */
    public function setValue($value)
    {
        $CurrentValue = $this->getTagProperty('value');
        if ($CurrentValue) {
            if ($CurrentValue == $value) {
                $this->setTagProperties(['checked']);
            }
        } else {
            $this->setTagProperties(['value' => $value]);
        }
    }

}
