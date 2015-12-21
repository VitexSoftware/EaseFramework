<?php

/**
 *  NavBar
 */

namespace Ease\TWB;

class Navbar extends \Ease\Html\Div
{

    /**
     * Vnitřek menu
     * @var \Ease\Html\Div
     */
    public $menuInnerContent = null;

    /**
     * Položky menu
     * @var \Ease\Html\UlTag
     */
    private $nav;

    /**
     * Položky menu přidávané vpravo
     * @var \Ease\Html\UlTag
     */
    private $navRight;

    /**
     * Menu aplikace
     *
     * @param string $name
     * @param string $brand
     * @param array  $properties
     */
    public function __construct($name = null, $brand = null, $properties = null)
    {
        if (is_null($properties)) {
            $properties = array('class' => 'navbar navbar-default');
        } else {
            if (isset($properties)) {
                $properties['class'] = 'navbar navbar-default ' . $properties['class'];
            } else {
                $properties['class'] = 'navbar navbar-default';
            }
        }
        $properties['role'] = 'navigation';
        $properties['name'] = $name;
        $this->menuInnerContent = parent::addItem(new \Ease\Html\Div( null, array('class' => 'navbar-inner')));
        parent::__construct(null, $properties);
        $this->addItem(self::NavBarHeader($name, $brand));
        $navCollapse = $this->addItem(new \Ease\Html\Div( null, array('class' => 'collapse navbar-collapse navbar-' . $name . '-collapse')));
        $this->nav = $navCollapse->addItem(new \Ease\Html\UlTag(null, array('class' => 'nav navbar-nav')));
        $this->tagType = 'nav';
        $pullRigt = new \Ease\Html\Div( null, array('class' => 'pull-right'));
        $this->navRight = $pullRigt->addItem(new \Ease\Html\UlTag(null, array('class' => 'nav navbar-nav nav-right')));
        $navCollapse->addItem($pullRigt);
        Part::twBootstrapize();
    }

    public static function NavBarHeader($handle, $brand)
    {
        $navstyle = '.navbar-' . $handle . '-collapse';
        $nbhc['button'] = new \Ease\Html\ButtonTag(array(new \Ease\Html\SpanTag(null, _('přepnutí navigace'), array('class' => 'sr-only')), new \Ease\Html\SpanTag(null, null, array('class' => 'icon-bar')), new \Ease\Html\SpanTag(null, null, array('class' => 'icon-bar')), new \Ease\Html\SpanTag(null, null, array('class' => 'icon-bar'))), array('type' => 'button', 'class' => 'navbar-toggle', 'data-toggle' => 'collapse', 'data-target' => $navstyle));
        if ($brand) {
            $nbhc['brand'] = new \Ease\Html\ATag('./', $brand, array('class' => 'navbar-brand'));
        }
        return new \Ease\Html\Div( $nbhc, array('class' => 'navbar-header'));
    }

    /**
     * Přidá položku do navigační lišty
     *
     * @param mixed  $Item         vkládaná položka
     * @param string $PageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return EasePage poiner to object well included
     */
    function &addItem($Item, $PageItemName = null)
    {
        $added = $this->menuInnerContent->addItem($Item, $PageItemName);
        return $added;
    }

    /**
     * Přidá položku menu
     *
     * @param \Ease\Html\ATag $pageItem Položka menu
     * @param string       $pull     'right' strká položku v menu do prava
     *
     * @return EaseWebPage
     */
    function &addMenuItem($pageItem, $pull = 'left')
    {
        if ($pull == 'left') {
            $menuItem = $this->nav->addItemSmart($pageItem);
        } else {
            $menuItem = $this->navRight->addItemSmart($pageItem);
        }
        if (isset($pageItem->tagProperties['href'])) {
            $href = basename($pageItem->tagProperties['href']);
            if (strstr($href, '?')) {
                list($targetPage, $params) = explode('?', $href);
            } else {
                $targetPage = $href;
            }
            if ($targetPage == basename(\Ease\Page::phpSelf())) {
                if ($pull == 'left') {
                    $this->nav->lastItem()->setTagProperties(array('class' => 'active'));
                } else {
                    $this->navRight->lastItem()->setTagProperties(array('class' => 'active'));
                }
            }
        }
        return $menuItem;
    }

    function &addDropDownSubmenu($name, $items)
    {
        $dropdown = $this->addItem(new \Ease\Html\UlTag(null, array('class' => 'dropdown-menu', 'role' => 'menu')));
        if (count($items)) {
            foreach ($items as $item) {
                $this->addMenuItem($item);
            }
        }
        return $dropdown;
    }

    /**
     * Vloží rozbalovací menu
     *
     * @param  string         $label popisek menu
     * @param  array|string   $items položky menu
     * @param  string         $pull  směr zarovnání
     * @return \Ease\Html\ULTag
     */
    function &addDropDownMenu($label, $items, $pull = 'left')
    {
        Part::twBootstrapize();
        \Ease\Shared::webPage()->addJavaScript('$(\'.dropdown-toggle\').dropdown();', null, true);
        $dropDown = new \Ease\Html\LiTag(null, array('class' => 'dropdown', 'id' => $label));
        $dropDown->addItem(new \Ease\Html\ATag('#' . $label . '', $label . '<b class="caret"></b>', array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown')));
        $dropDownMenu = $dropDown->addItem(new \Ease\Html\UlTag(null, array('class' => 'dropdown-menu')));
        if (is_array($items)) {
            foreach ($items as $target => $label) {
                if (is_array($label)) {
                    //Submenu
                    $dropDownMenu->addItem($this->addDropDownSubmenu($target, $label));
                } else {
                    //Item
                    if (!$target) {
                        $dropDownMenu->addItem(new \Ease\Html\LiTag(null, array('class' => 'divider')));
                    } else {
                        $dropDownMenu->addItemSmart(new \Ease\Html\ATag($target, $label));
                    }
                }
            }
        } else {
            $dropDownMenu->addItem($items);
        }
        if ($pull == 'left') {
            $this->nav->addItemSmart($dropDown);
        } else {
            $this->navRight->addItemSmart($dropDown);
        }
        return $dropDown;
    }

}
