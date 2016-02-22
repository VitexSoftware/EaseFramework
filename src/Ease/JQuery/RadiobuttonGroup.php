<?php

namespace Ease\JQuery;

/**
 * A set of radio buttons transformed into a button set.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @link   http://jqueryui.com/demos/button/#radio
 */
class RadiobuttonGroup extends Ease\Html\RadiobuttonGroup
{

    /**
     * Doplní popisek prvku
     *
     * @param string $label
     */
    public function addLabel($label = null)
    {
        $ForID = $this->lastItem->getTagID();
        if (is_null($label)) {
            $label = $ForID;
        }
        $this->addItem('<label for="' . $ForID . '">' . $label . '</label>');
    }

    /**
     * Doplní podporu pro jQueryUI
     */
    public function finalize()
    {
        UIPart::jQueryze($this);
        $enclosure = new Ease\Html\Div($this->pageParts, ['id' => $this->Name . 'Group']);
        unset($this->pageParts);
        $this->addItem($enclosure);
        $this->addJavaScript('$(function () { $( "#' . $enclosure->getTagID() . '" ).buttonset(); } );', null, true);
    }
}
