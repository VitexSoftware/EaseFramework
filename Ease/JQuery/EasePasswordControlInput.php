<?php

/**
 * Vloží pole pro zadávání hesla s kontrolou zdali souhlasí
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EasePasswordControlInput extends Ease\Html\InputPasswordTag
{
    /**
     * Vloží pole pro zadávání hesla s kontrolou zdali souhlasí
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
        $this->includeJavaScript('jquery.password-strength.js', null, true);
        $this->addCSS('
.password_control {
    padding: 0 5px;
    display: inline-block;
    }
.password_control_0 {
    background-color: #fcb6b1;
    }
.password_control_1 {
    background-color: #bcfcb1;
    }
');
        $this->addJavaScript('$(\'#' . $this->getTagID() . '\').password_control();', null, true);
    }
}