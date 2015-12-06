<?php

/**
 * InPlace Editor part
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseInPlaceEditor extends EaseJQueryUIPart
{
    /**
     * Políčko editoru
     * @var Ease\Html\InputTextTag
     */
    public $EditorField = null;
    /**
     * Script zpracovávající odeslaná data
     * @var string
     */
    public $SubmitTo = null;
    /**
     * Inplace Editor
     *
     * @param name   $name
     * @param string $Content
     * @param string $SubmitTo
     * @param array  $Properties
     */
    public function __construct($name, $Content, $SubmitTo = null, $Properties = null)
    {
        parent::__construct($name, $Content, $Properties);
        if (!$SubmitTo) {
            $this->SubmitTo = str_replace('.php', 'Ajax.php', $_SERVER['PHP_SELF']);
        } else {
            $this->SubmitTo = $SubmitTo;
        }
        $this->EditorField = $this->addItem(new Ease\Html\InputTextTag($name, $Content, $Properties));
        $this->EditorField->setTagID();
    }
    /**
     * Vložení javascriptů
     */
    public function finalize()
    {
        $this->includeJavaScript('jquery-editinplace.js', 2, true);
    }
    /**
     * Vykreslení
     */
    public function draw()
    {
        parent::draw();
        $JavaScript = new EaseJavaScript('$("#' . $this->EditorField->getTagID() . '").editInPlace({ url: "' . $this->SubmitTo . '", show_buttons: true }); ');
        $JavaScript->draw();
    }
}