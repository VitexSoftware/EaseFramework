<?php

/**
 * Vloží pole pro zadávání s měřičem jeho síly
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EasePasswordInput extends Ease\Html\InputPasswordTag
{
    /**
     * Vloží pole pro zadávání s měřičem jeho síly
     *
     * @param string $name
     * @param string $value
     * @param array  $Properties
     */
    public function __construct($name, $value = null, $Properties = null)
    {
        parent::__construct($name, $value, $Properties);
        $this->setTagID($name);
    }
    /**
     * Vloží styly a scripty
     */
    public function finalize()
    {
        EaseJQueryPart::jQueryze($this);
        $this->includeJavaScript('password-strength.js', null, true);
        $this->addCSS('
.password_strength {
    padding: 0 5px;
    display: inline-block;
    }
.password_strength_1 {
    background-color: #fcb6b1;
    }
.password_strength_2 {
    background-color: #fccab1;
    }
.password_strength_3 {
    background-color: #fcfbb1;
    }
.password_strength_4 {
    background-color: #dafcb1;
    }
.password_strength_5 {
    background-color: #bcfcb1;
    }
');
        $this->addJavaScript('$(\'#' . $this->getTagID() . '\').password_strength();', null, true);
    }
}