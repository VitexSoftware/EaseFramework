<?php

/**
 * TinyMce komponenta
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @todo add include files
 */
class EaseTinyMCE extends Ease\Html\TextareaTag
{
    /**
     * Vložení těla sktiptu
     */
    public function afterAdd()
    {
        $this->setTagID($this->getTagName());
        $this->webPage->includeJavaScript('includes/javascript/tiny_mce/tiny_mce.js');
        $this->webPage->addJavaScript('
tinyMCE.init({
mode : "textareas",
theme : "simple"
});
');
    }
}