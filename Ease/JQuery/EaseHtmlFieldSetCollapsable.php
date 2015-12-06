<?php

/**
 * Click to frameset title to collapse in line
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @filesource http://michael.theirwinfamily.net/demo/jquery/collapsible-fieldset/index.html
 */
namespace Ease\Html; 
 class FieldSetCollapsable extends Ease\Html\FieldSet
{
    /**
     * Vykreslit fieldset zavřený ?
     * @var boolean
     */
    private $Closed = false;
    /**
     * Collapsible Fieldset
     *
     * @param string  $Legend
     * @param mixed   $Content
     * @param string  $TagID
     * @param boolean $Closed
     */
    public function __construct($Legend, $Content = null, $TagID = null, $Closed = true)
    {
        $this->Closed = $Closed;
        parent::__construct($Legend, $Content);
        if (is_null($TagID)) {
            $TagID = EaseBrick::randomString();
        }
        $this->setTagID($TagID);
    }
    /**
     * Přidá javascripty
     */
    public function finalize()
    {
        EaseJQueryPart::jQueryze($this);
        EaseShared::webPage()->includeJavaScript('collapsible.js', 4, true);
        EaseShared::webPage()->includeCss('collapsible.css', true);
        EaseShared::webPage()->addJavaScript('$(\'#' . $this->getTagID() . '\').collapse({ closed: ' . ($this->Closed ? 'true' : 'false') . ' });', null, true);
    }
}