<?php

/**
 * Toggle checboxes within
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseCheckboxToggler extends Ease\Html\DivTag
{
    public function finalize()
    {
        EaseJQueryPart::jQueryze($this);
        $this->addItem('<input class="button" value="☑ Označit vše" type="button" name="checkAllAuto" onClick="jQuery(\'#' . $this->getTagID() . ' :checkbox:not(:checked)\').attr(\'checked\', \'checked\');" id="checkAllAuto"/>');
        $this->addItem('<input class="button" value="☐ Odznačit vše" type="button" name="checkAllAuto" onClick="jQuery(\'#' . $this->getTagID() . ' :checkbox:checked\').removeAttr(\'checked\', \'checked\');" id="checkAllAuto"/>');
    }
}