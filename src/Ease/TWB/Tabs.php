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
     * @param array  $tagProperties
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
     * @return \Ease\Html\DivTag odkaz na vložený obsah
     */
    public function &addTab($tabName, $tabContent = null, $active = false)
    {
        if (is_null($tabContent)) {
            $tabContent = new \Ease\Html\DivTag();
        }
        $this->tabs[$tabName] = ['static' => $tabContent];
        if ($active) {
            $this->activeTab = $tabName;
        }

        return $this->tabs[$tabName]['static'];
    }

    /**
     * Create new Dynamic Tab
     *
     * @param string $tabName    jméno a titulek tabu
     * @param string $tabUrl     where to obtain tab content
     * @param bool   $active     Má být tento tab aktivní ?
     *
     * @return \Ease\Html\DivTag odkaz na vložený obsah
     */
    public function &addAjaxTab($tabName, $tabUrl, $active = false)
    {
        $this->tabs[$tabName] = ['ajax' => $tabUrl];
        if ($active) {
            $this->activeTab = $tabName;
        }
        \Ease\Shared::webPage()->addJavaScript('
$(\'#'.$this->getTagID().' a\').click(function (e) {
	e.preventDefault();

	var url = $(this).attr("data-url");
  	var href = this.hash;
  	var pane = $(this);

	// ajax load from data-url
	$(href).load(url,function(result){
	    pane.tab(\'show\');
	});
});
            
');
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
        foreach ($this->tabs as $tabName => $tab) {
            $tabProperties = ['data-toggle' => 'tab'];
            if (key($tab) == 'ajax') {
                $tabProperties['data-url'] = current($tab);
            }
            $anchor = '#'.\Ease\Brick::lettersOnly( str_replace(['(',')'],'',$this->partName.$tabName));
            if ($tabName == $this->activeTab) {
                $tabsUl->addItem(new \Ease\Html\LiTag(new \Ease\Html\ATag($anchor,
                            $tabName, $tabProperties), ['class' => 'active']));
            } else {
                $tabsUl->addItem(new \Ease\Html\LiTag(new \Ease\Html\ATag($anchor,
                            $tabName, $tabProperties)));
            }
        }
        $tabDiv = $this->addItem(new \Ease\Html\DivTag(null,
                ['id' => $this->partName.'body', 'class' => 'tab-content']));
        foreach ($this->tabs as $tabName => $tab) {
            switch (key($tab)) {
                case 'static':
                    $tabContent = current($tab);
                    break;
                case 'ajax':
                    $tabContent = '';
                    break;
            }

            if ($tabName == $this->activeTab) {
                $tabDiv->addItem(new \Ease\Html\DivTag($tabContent,
                        ['id' => $this->partName.\Ease\Brick::lettersOnly($tabName),
                        'class' => 'tab-pane active',]));
            } else {
                $tabDiv->addItem(new \Ease\Html\DivTag($tabContent,
                        ['id' => $this->partName.\Ease\Brick::lettersOnly($tabName),
                        'class' => 'tab-pane',]));
            }
        }
        Part::twBootstrapize();


        if (key($this->tabs[$this->activeTab]) == 'ajax') {
            \Ease\Shared::webPage()->addJavaScript('
// load first tab content
$(\'#'.$this->partName.$this->activeTab.'\').load($(\'.active a\').attr("data-url"),function(result){
  $(\'.active a\').tab(\'show\');
});
');
        } else {
            \Ease\Shared::webPage()->addJavaScript(
                '
        $(\'#'.$this->partName.' a[href="#'.\Ease\Brick::lettersOnly($this->activeTab).'"]\').tab(\'show\');
', null, true
            );
        }
    }
}
