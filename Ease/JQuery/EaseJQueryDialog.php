<?php

/**
 * Dialog
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @todo dodělat #IDčka ...
 */
class EaseJQueryDialog extends EaseJQueryUIPart
{
    /**
     * ID divu s dialogem
     * @var string
     */
    public $DialogID = NULL;
    /**
     * Titulek okna
     * @var string
     */
    public $Title = '';
    /**
     * Zpráva zobrazená v dialogu
     * @var type
     */
    public $Message = '';
    /**
     * Ikona zprávy
     * @var type
     */
    public $Icon = '';
    /**
     * Doplnující informace
     * @var type
     */
    public $Notice = NULL;
    /**
     * jQuery dialog
     *
     * @param string $DialogID id divu s dialogem
     * @param string $Title    titulek okna
     * @param mixed  $Message  obsah dialogu
     * @param string $Icon     jQueryUI ikona
     * @param string $Notice   doplnující informce
     */
    public function __construct($DialogID, $Title, $Message, $Icon = 'ui-icon-circle-check', $Notice = NULL)
    {
        $this->DialogID = $DialogID;
        $this->Title = $Title;
        $this->Message = $Message;
        $this->Icon = $Icon;
        $this->Notice = $Notice;
        $this->partProperties = array('modal' => true, 'buttons' => array('Ok' => 'function () { $( this ).dialog( "close" ); }'));
        parent::__construct();
    }
    /**
     * Nastaveni javascriptu
     */
    public function onDocumentReady()
    {
        return '$("#' . $this->DialogID . '").dialog( {' . EaseJQueryPart::partPropertiesToString($this->partProperties) . '} )';
    }
    /**
     * Seskládání HTML
     */
    public function finalize()
    {
        $DialogDiv = $this->addItem(new Ease\Html\DivTag($this->DialogID, NULL, array('title' => $this->Title)));
        $DialogMessage = $DialogDiv->addItem(new Ease\Html\PTag());
        $DialogMessage->addItem(new Ease\Html\SpanTag(NULL, NULL, array('class' => 'ui-icon ' . $this->Icon, 'style' => 'float:left; margin:0 7px 50px 0;')));
        $DialogMessage->addItem($this->Message);
        if (!is_null($this->Notice)) {
            $DialogDiv->addItem(new Ease\Html\PTag($this->Notice));
        }
        parent::finalize();
    }
}