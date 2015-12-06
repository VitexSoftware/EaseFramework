<?php

/*
 $this->webPage->IncludeJavaScript('http://jqueryui.com/themeroller/themeswitchertool/');
 $this->webPage->addJavaScript('$(\'#switcher\').themeswitcher();',null,true);
 $this->addItem(new Ease\Html\DivTag('switcher'));
*/
/**
 class EaseSimpleScrollerPart extends EaseJQueryUIPart
 {
 }
*/
/**
 * Vstupní prvek pro soubor
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseAjaxFileInput extends Ease\Html\InputFileTag
{
    public $UploadTarget = '';
    public $UploadDoneCode = '$(res).insertAfter(this);';
    /**
     * Komponenta pro Upload souboru
     *
     * @param string $name
     * @param string $UploadTarget
     * @param string $value
     */
    public function __construct($name, $UploadTarget, $value = null)
    {
        $this->UploadTarget = $UploadTarget;
        parent::__construct($name, $value);
        $this->setTagID($name);
    }
    public function finalize()
    {
        $this->includeJavaScript('jquery.js', 0, true);
        $this->includeJavaScript('jquery.upload.js', 4, true);
        $this->addJavaScript('
 $(\'#' . $this->getTagID() . '\').change(function () {
    $(this).upload(\'' . $this->UploadTarget . '\', function (res) {
        ' . $this->UploadDoneCode . '
    }, \'html\');
});
', null, true);
    }
    public function setUpDoneCode($DoneCode)
    {
        $this->UploadDoneCode = $DoneCode;
    }
}