<?php
/**
 * Vypisuje stavové hlášky.
 */

namespace Ease\TWB;

class StatusMessages extends \Ease\Html\DivTag
{

    /**
     * Blok stavových zpráv.
     * Status message block
     */
    public function __construct()
    {
        $properties['class'] = 'well';
        $properties['id']    = 'StatusMessages';
        $properties['title'] = _('Click to hide messages');
        $properties['style'] = 'padding-top: 40px; padding-bottom: 0px;';
        parent::__construct(null, null, $properties);
        \Ease\JQuery\Part::jQueryze();
        $this->addJavaScript('$("#StatusMessages").click(function () { $("#StatusMessages").fadeTo("slow",0.25).slideUp("slow"); });',
            3, true);
    }

    /**
     * Vypíše stavové zprávy.
     * Print status messafes
     */
    public function draw()
    {
        $statusMessages = trim(\Ease\Shared::webPage()->getStatusMessagesAsHtml());
        if (strlen($statusMessages)) {
            parent::addItem($statusMessages);
            parent::draw();
        } else {
            $this->suicide();
        }
    }
}
