<?php

namespace Ease\Example\TWB;

class AppStatusMessages extends \Ease\Html\Div
{
    /**
     * Blok stavových zpráv.
     */
    public function __construct()
    {
        $properties['class'] = 'well';
        $properties['id'] = 'StatusMessages';
        $properties['title'] = _('kliknutím skryjete zprávy');
        $properties['style'] = 'padding-top: 40px; padding-bottom: 0px;';
        parent::__construct(null, null, $properties);
        $this->addJavaScript(
            '$("#StatusMessages").click(function () {
            $("#StatusMessages").fadeTo("slow",0.25).slideUp("slow");
            });', 3, true
        );
    }

    /**
     * Vypíše stavové zprávy.
     */
    public function draw()
    {
        $statusMessages = trim($this->webPage->getStatusMessagesAsHtml());
        if ($statusMessages) {
            parent::addItem($statusMessages);
        } else {
            $this->suicide();
        }
        parent::draw();
    }
}
