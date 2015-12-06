<?php

/**
 * Input for Date and time
 * @link http://trentrichardson.com/examples/timepicker/
 * @package EaseFrameWork
 * @author vitex
 */
class EaseDateTimeSelector extends EaseJQueryUIPart
{
    /**
     * Propetries pass to Input
     * @var array
     */
    public $tagProperties = NULL;
    /**
     * Initial datetime
     * @var string
     */
    public $InitialValue = NULL;
    /**
     * Datetime Picker parameters
     * @var array
     */
    public $partProperties = array('dateFormat' => 'yy-mm-dd', 'showSecond' => true, 'timeFormat' => 'hh:mm:ss');
    /**
     * Text Input
     * @var Ease\Html\InputTextTag
     */
    public $InputTag = NULL;
    /**
     * Input for Date and time
     * @param string $partName
     */
    public function __construct($partName, $InitialValue = NULL, $tagProperties = NULL)
    {
        $this->tagProperties = $tagProperties;
        $this->InitialValue = $InitialValue;
        $this->SetPartName($partName);
        parent::__construct();
        $this->easeShared->webPage->IncludeJavaScript('jquery-ui-timepicker-addon.js', 3, true);
        $this->easeShared->webPage->IncludeCss('jquery-ui-timepicker-addon.css', null, true);
        $this->InputTag = new Ease\Html\InputTextTag($this->partName, $this->InitialValue, $this->tagProperties);
        $this->InputTag->setTagID($this->partName);
        $this->InputTag = $this->addItem($this->InputTag);
    }
    /**
     * Vložení skriptu
     */
    public function finalize()
    {
        $this->easeShared->webPage->addJavaScript('$(function () { $( "#' . $this->partName . '" ).datetimepicker( { ' . $this->GetPartPropertiesToString() . ' });});', 10);
    }
}