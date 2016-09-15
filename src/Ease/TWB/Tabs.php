<?php

namespace Ease\TWB;

/**
 * Create TWBootstrap tabs.
 *
 * @see    http://getbootstrap.com/2.3.2/components.html#navs
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class Tabs extends \Ease\Container
{
    /**
     * Název.
     *
     * @var string
     */
    public $partName = 'TWBTabs';

    /**
     * Array of tab names=>contents.
     *
     * @var array
     */
    public $tabs = [];

    /**
     * Jméno aktivního tabu.
     *
     * @var string
     */
    private $activeTab = null;

    /**
     * Create TWBootstrap tabs.
     *
     * @param string $partName       - DIV id
     * @param array  $tabsList
     * @param array  $partProperties
     */
    public function __construct($partName, $tabsList = null,
                                $tagProperties = null)
    {
        $this->partName = $partName;
        parent::__construct();
        if (is_array($tabsList)) {
            $this->tabs = array_merge($this->tabs, $tabsList);
        }
        if (!is_null($tagProperties)) {
            $this->setPartProperties($tagProperties);
        }
    }

    /**
     * Vytvoří nový tab a vloží do něj obsah.
     *
     * @param string $tabName    jméno a titulek tabu
     * @param mixed  $tabContent
     * @param bool   $active     Má být tento tab aktivní ?
     *
     * @return pointer odkaz na vložený obsah
     */
    public function &addTab($tabName, $tabContent = null, $active = false)
    {
        if (is_null($tabContent)) {
            $tabContent = new \Ease\Html\Div();
        }
        $this->tabs[$tabName] = $tabContent;
        if ($active) {
            $this->activeTab = $tabName;
        }

        return $this->tabs[$tabName];
    }

    /**
     * Vrací ID tagu.
     *
     * @return string
     */
    public function getTagID()
    {
        return $this->partName;
    }

    /**
     * Vložení skriptu a divů do stránky.
     */
    public function finalize()
    {
        if (is_null($this->activeTab)) {
            $this->activeTab = current(array_keys($this->tabs));
        }
        $tabsUl = $this->addItem(new \Ease\Html\UlTag(null,
            ['class' => 'nav nav-tabs', 'id' => $this->partName]));
        foreach ($this->tabs as $tabName => $tabContent) {
            if ($tabName == $this->activeTab) {
                $tabsUl->addItem(new \Ease\Html\LiTag(new \Ease\Html\ATag('#'.$this->partName.\Ease\Brick::lettersOnly($tabName),
                    $tabName, ['data-toggle' => 'tab']), ['class' => 'active']));
            } else {
                $tabsUl->addItem(new \Ease\Html\LiTag(new \Ease\Html\ATag('#'.$this->partName.\Ease\Brick::lettersOnly($tabName),
                    $tabName, ['data-toggle' => 'tab'])));
            }
        }
        $tabDiv = $this->addItem(new \Ease\Html\Div(null,
            ['id' => $this->partName.'body', 'class' => 'tab-content']));
        foreach ($this->tabs as $tabName => $tabContent) {
            if ($tabName == $this->activeTab) {
                $tabDiv->addItem(new \Ease\Html\Div($tabContent,
                    ['id' => $this->partName.\Ease\Brick::lettersOnly($tabName),
                    'class' => 'tab-pane active',]));
            } else {
                $tabDiv->addItem(new \Ease\Html\Div($tabContent,
                    ['id' => $this->partName.\Ease\Brick::lettersOnly($tabName),
                    'class' => 'tab-pane',]));
            }
        }
        Part::twBootstrapize();
        \Ease\Shared::webPage()->addJavaScript(
            '
        $(\'#'.$this->partName.' a[href="#'.\Ease\Brick::lettersOnly($this->activeTab).'"]\').tab(\'show\');
', null, true
        );
    }
}
