<?php

namespace Ease\Html;

/**
 * Vstupní prvek pro odeslání souboru.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class InputFileTag extends InputTag
{

    /**
     * Vstupní box pro volbu souboru.
     *
     * @param string $name  jméno tagu
     * @param string $value předvolená hodnota
     */
    public function __construct($name, $value = null)
    {
        parent::__construct($name, $value);
        $this->setTagProperties(['type' => 'file']);
    }
}
