<?php

namespace Ease\Html;

/**
 * Vstup pro zadání hesla.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class InputPasswordTag extends InputTextTag
{

    /**
     * Input pro heslo.
     *
     * @param string $name  jméno tagu
     * @param string $value předvolené heslo
     */
    public function __construct($name, $value = null)
    {
        parent::__construct($name, $value);
        $this->setTagProperties(['type' => 'password']);
    }
}
