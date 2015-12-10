<?php

/**
 * Vypisuje stavové hlášky
 */
namespace Ease\TWB; 
 class StatusMessages extends \Ease\Html\DivTag
{
    /**
     * Blok stavových zpráv
     */
    public function __construct()
    {
        $properties['class'] = 'well';
        $properties['id'] = 'StatusMessages';
        $properties['title'] = _('kliknutím skryjete zprávy');
        $properties['style'] = 'padding-top: 40px; padding-bottom: 0px;';
        parent::__construct(null, null, $properties);
        EaseJQueryPart::jQueryze();
        $this->addJavaScript('$("#StatusMessages").click(function () { $("#StatusMessages").fadeTo("slow",0.25).slideUp("slow"); });', 3, true);
    }
    /**
     * Vypíše stavové zprávy
     */
    public function draw()
    {
        $StatusMessages = trim($this->webPage->getStatusMessagesAsHtml());
        if ($StatusMessages) {
            parent::addItem($StatusMessages);
            parent::draw();
        } else {
            $this->suicide();
        }
    }
}