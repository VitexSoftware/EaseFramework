<?php

/**
 * Color picker Part
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseJQColorPicker extends Ease\Html\InputTextTag
{
    public function finalize()
    {
        $this->setTagID($this->getTagName());
        $this->webPage->includeJavaScript('jquery.js', 0);
        $this->webPage->includeJavaScript('colorpicker.js');
        $this->webPage->includeCss('colorpicker.css');
        $this->webPage->addJavaScript('$(document).ready(function () {
    $(\'#' . $this->getTagID() . '\').ColorPicker({
    onSubmit: function (hsb, hex, rgb, el) {
        $(el).val(hex);
        $(el).ColorPickerHide();
    },
    onBeforeShow: function () {
        $(this).ColorPickerSetColor(this.value);
    }
    })
    .bind(\'keyup\', function () {
    $(this).ColorPickerSetColor(this.value);
    });

 });
', 3);
    }
}