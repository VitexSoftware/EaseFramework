<?php

namespace Ease\JQuery;

/**
 * jQueryUI Dialog.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
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
    public $icon = '';

    /**
     * Doplnující informace.
     *
     * @var type
     */
    public $notice = null;

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
        $this->dialogID = $dialogID;
        $this->title = $title;
        $this->message = $message;
        $this->icon = $icon;
        $this->notice = $notice;
        $this->partProperties = ['modal' => true, 'buttons' => ['Ok' => 'function () { $( this ).dialog( "close" ); }']];
        parent::__construct();
    }

    /**
     * Nastaveni javascriptu.
     */
    public function onDocumentReady()
    {
        return '$("#'.$this->dialogID.'").dialog( '.json_encode($this->partProperties).' )';
    }

    /**
     * Seskládání HTML.
     */
    public function finalize()
    {
        $dialogDiv = $this->addItem(new \Ease\Html\Div(null,
            ['id' => $this->dialogID, 'title' => $this->title]));
        $dialogMessage = $dialogDiv->addItem(new \Ease\Html\PTag());
        $dialogMessage->addItem(new \Ease\Html\Span(null,
            ['class' => 'ui-icon '.$this->icon, 'style' => 'float:left; margin:0 7px 50px 0;']));
        $dialogMessage->addItem($this->message);
        if (!is_null($this->notice)) {
            $dialogDiv->addItem(new \Ease\Html\PTag($this->notice));
        }
        parent::finalize();
    }
}
