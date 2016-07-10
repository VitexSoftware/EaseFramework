<?php

namespace Ease\JQuery;

/**
 * Dialog.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 *
 * @todo   dodělat #IDčka ...
 */
class Dialog extends UIPart
{
    /**
     * ID divu s dialogem.
     *
     * @var string
     */
    public $dialogID = null;

    /**
     * Titulek okna.
     *
     * @var string
     */
    public $title = '';

    /**
     * Zpráva zobrazená v dialogu.
     *
     * @var type
     */
    public $message = '';

    /**
     * Ikona zprávy.
     *
     * @var type
     */
    public $Icon = '';

    /**
     * Doplnující informace.
     *
     * @var type
     */
    public $Notice = null;

    /**
     * jQuery dialog.
     *
     * @param string $dialogID id divu s dialogem
     * @param string $title    titulek okna
     * @param mixed  $message  obsah dialogu
     * @param string $icon     jQueryUI ikona
     * @param string $notice   doplnující informce
     */
    public function __construct($dialogID, $title, $message,
                                $icon = 'ui-icon-circle-check', $notice = null)
    {
        $this->dialogID       = $dialogID;
        $this->title          = $title;
        $this->message        = $message;
        $this->Icon           = $icon;
        $this->Notice         = $notice;
        $this->partProperties = ['modal' => true, 'buttons' => ['Ok' => 'function () { $( this ).dialog( "close" ); }']];
        parent::__construct();
    }

    /**
     * Nastaveni javascriptu.
     */
    public function onDocumentReady()
    {
        return '$("#'.$this->dialogID.'").dialog( {'.Part::partPropertiesToString($this->partProperties).'} )';
    }

    /**
     * Seskládání HTML.
     */
    public function finalize()
    {
        $DialogDiv     = $this->addItem(new Ease\Html\Div(null,
            ['id' => $this->dialogID, 'title' => $this->title]));
        $Dialogmessage = $DialogDiv->addItem(new \Ease\Html\PTag());
        $Dialogmessage->addItem(new \Ease\Html\Span(null,
            ['class' => 'ui-icon '.$this->Icon, 'style' => 'float:left; margin:0 7px 50px 0;']));
        $Dialogmessage->addItem($this->message);
        if (!is_null($this->Notice)) {
            $DialogDiv->addItem(new \Ease\Html\PTag($this->Notice));
        }
        parent::finalize();
    }
}