<?php

/**
 * Posunovatelný blok
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EasejQueryScroller extends Ease\Html\DivTag
{
    /**
     * Objekt do nejž se vkládá rolovaný
     * @var type
     */
    public $ScrollableArea = null;
    /**
     * Rolovatelná oblast
     *
     * @param string         $name
     * @param EasePage|mixed $Content
     * @param array          $Properties
     */
    public function __construct($name = null, $Content = null, $Properties = null)
    {
        $Properties['id'] = $name;
        parent::__construct($name, $Content, $Properties);
        parent::addItem(new Ease\Html\DivTag(null, null, array('class' => 'scrollingHotSpotLeft')));
        parent::addItem(new Ease\Html\DivTag(null, null, array('class' => 'scrollingHotSpotRight')));
        $ScrollWrapper = parent::addItem(new Ease\Html\DivTag(null, null, array('class' => 'scrollWrapper')));
        $this->ScrollableArea = $ScrollWrapper->addItem(new Ease\Html\DivTag(null, null, array('class' => 'scrollableArea')));
    }
    /**
     * Vloží javascripty a csska
     */
    public function finalize()
    {
        EaseJQueryUIPart::jQueryze($this);
        EaseShared::webPage()->includeCss('smoothDivScroll.css', true);
        EaseShared::webPage()->includeJavaScript('jquery.smoothDivScroll-1.1.js', null, true);
        EaseShared::webPage()->addJavaScript('
        $(function () {
            $("div#' . $this->getTagID() . '").smoothDivScroll({});
        });
        ');
    }
    /**
     * Vkládá položky do skrolovatelné oblasti
     *
     * @param mixed $PageItem
     *
     * @return object|mixed
     */
    function &addItem($PageItem, $PageItemName = null)
    {
        return $this->ScrollableArea->addItem($PageItem, $PageItemName);
    }
}