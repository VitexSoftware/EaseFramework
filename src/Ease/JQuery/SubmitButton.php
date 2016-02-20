<?php

namespace Ease\JQuery;

/**
 * Odesílací tlačítko
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @link http://jqueryui.com/demos/button/
 */
class SubmitButton extends UIPart {

    /**
     * Jméno tlačítka
     * @var string
     */
    public $name = null;

    /**
     * Paramatry pro jQuery .button()
     * @var array
     */
    public $JQOptions = null;

    /**
     * Odkaz na objekt tlačítka
     * @var Ease\Html\InputSubmitTag
     */
    public $Button = null;

    /**
     * Odesílací tlačítko
     *
     * @see http://jqueryui.com/demos/button/
     * @param string       $name
     * @param string       $value
     * @param string       $Title
     * @param array|string $JQOptions  parametry pro $.button()
     * @param array        $Properties vlastnosti HTML tagu
     */
    public function __construct($name, $value, $Title = null, $JQOptions = null, $Properties = null) {
        parent::__construct();
        $this->Name = $name;
        $this->JQOptions = $JQOptions;
        $Properties['title'] = $Title;
        $this->Button = $this->addItem(new \Ease\Html\InputSubmitTag($name, $value, $Properties));
    }

    /**
     * Nastaveni javascriptu
     */
    public function onDocumentReady() {
        return '$("input[name=' . $this->Name . ']").button( {' . Part::partPropertiesToString($this->JQOptions) . '} )';
    }

    /**
     * Nastaví classu tagu
     *
     * @param string $ClassName
     */
    public function setTagClass($ClassName) {
        return $this->Button->setTagClass($ClassName);
    }

    /**
     * Nastaví jméno tagu
     *
     * @param string $TagName
     */
    public function setTagName($TagName) {
        return $this->Button->setTagName($TagName);
    }

}
