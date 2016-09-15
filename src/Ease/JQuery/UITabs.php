<?php

namespace Ease\JQuery;

/**
 * Create jQueryUI tabs.
 *
 * @see    http://jqueryui.com/demos/tabs/
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class UITabs extends UIPart
{
    /**
     * Array of tab names=>contents.
     *
     * @var array
     */
    public $Tabs = [];

    /**
     * Create jQueryUI tabs.
     *
     * @param string $partName       - DIV id
     * @param array  $tabsList
     * @param array  $partProperties
     */
    public function __construct($partName, $tabsList = null,
                                $partProperties = null)
    {
        $this->setPartName($partName);
        parent::__construct();
        if (is_array($tabsList)) {
            $this->Tabs = array_merge($this->Tabs, $tabsList);
        }
        if (!is_null($partProperties)) {
            $this->setPartProperties($partProperties);
        }
    }

    /**
     * Vytvoří nový tab a vloží do něj obsah.
     *
     * @param string $TabName    jméno a titulek tabu
     * @param mixed  $TabContent
     *
     * @return pointer odkaz na vložený obsah
     */
    public function &addTab($TabName, $TabContent = '')
    {
        $this->Tabs[$TabName] = $TabContent;

        return $this->Tabs[$TabName];
    }

    /**
     * Add dynamicaly loaded content.
     *
     * @param string $TabName
     * @param string $Url
     */
    public function addAjaxTab($TabName, $Url)
    {
        $this->Tabs[$TabName] = 'url:'.$Url;
    }

    /**
     * Vložení skriptu a divů do stránky.
     */
    public function finalize()
    {
        $this->addJavaScript('$(function () { $( "#'.$this->partName.'" ).tabs( {'.$this->getPartPropertiesToString().'} ); });',
            null, true);
        $Div   = $this->addItem(new \Ease\Html\Div(null,
            ['id' => $this->partName]));
        $UlTag = $Div->addItem(new \Ease\Html\UlTag());
        $Index = 0;
        foreach ($this->Tabs as $TabName => $TabContent) {
            if (!strlen($TabContent) || substr_compare($TabContent, 'url:', 0, 4)) {
                $UlTag->addItem(new \Ease\Html\ATag('#'.$this->partName.'-'.++$Index,
                    $TabName));
                $Div->addItem(new \Ease\Html\Div(null,
                    ['id' => $this->partName.'-'.$Index]));
                $Div->addToLastItem($TabContent);
            } else {
                $UlTag->addItem(new \Ease\Html\ATag(str_replace('url:', '',
                        $TabContent), $TabName));
                $Div->addItem(new \Ease\Html\Div(null,
                    ['id' => $this->partName.'-'.$Index]));
            }
        }
        self::jQueryze($this);
    }
}
