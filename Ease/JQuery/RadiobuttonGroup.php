<?php

namespace Ease\JQuery;

/**
 * A set of radio buttons transformed into a button set.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @link http://jqueryui.com/demos/button/#radio
 */
class RadiobuttonGroup extends Ease\Html\RadiobuttonGroup
{

    /**
     * Doplní popisek prvku
     *
     * @param string $Label
     */
    public function addLabel($Label = null)
    {
        $ForID = $this->lastItem->getTagID();
        if (is_null($Label)) {
            $Label = $ForID;
        }
        $this->addItem('<label for="' . $ForID . '">' . $Label . '</label>');
    }

    /**
     * Doplní podporu pro jQueryUI
     */
    public function finalize()
    {
        UIPart::jQueryze($this);
        $Enclosure = new Ease\Html\DivTag($this->Name . 'Group', $this->pageParts);
        unset($this->pageParts);
        $this->addItem($Enclosure);
        $this->addJavaScript('$(function () { $( "#' . $Enclosure->getTagID() . '" ).buttonset(); } );', null, true);
    }

}
