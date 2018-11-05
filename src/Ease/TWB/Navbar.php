<?php
/**
 *  NavBar.
 */

namespace Ease\TWB;

class Navbar extends \Ease\Html\DivTag
{
    /**
     * Vnitřek menu.
     *
     * @var \Ease\Html\DivTag
     */
    public $menuInnerContent = null;

    /**
     * Položky menu.
     *
     * @var \Ease\Html\UlTag
     */
    private $nav;

    /**
     * Položky menu přidávané vpravo.
     *
     * @var \Ease\Html\UlTag
     */
    private $navRight;

    /**
     * Menu aplikace.
     *
     * @param string $name
     * @param string $brand
     * @param array  $properties
     */
    public function __construct($name = null, $brand = null, $properties = [])
    {
        if (is_array($properties) && array_key_exists('class', $properties)) {
            $originalClass = $properties['class'];
        } else {
            $originalClass = '';
        }

        $properties['class']    = trim('navbar navbar-default '.$originalClass);
        $properties['role']     = 'navigation';
        $properties['name']     = $name;
        $this->menuInnerContent = parent::addItem(new \Ease\Html\DivTag(null,
                    ['class' => 'navbar-inner']));
        parent::__construct(null, $properties);
        $this->addItem(self::navBarHeader($name, $brand));
        $navCollapse            = $this->addItem(new \Ease\Html\DivTag(null,
                ['class' => 'collapse navbar-collapse navbar-'.$name.'-collapse']));
        $this->nav              = $navCollapse->addItem(new \Ease\Html\UlTag(null,
                ['class' => 'nav navbar-nav']));
        $this->tagType          = 'nav';
        $pullRigt               = new \Ease\Html\DivTag(null,
            ['class' => 'pull-right']);
        $this->navRight         = $pullRigt->addItem(new \Ease\Html\UlTag(null,
                ['class' => 'nav navbar-nav nav-right']));
        $navCollapse->addItem($pullRigt);
        Part::twBootstrapize();
    }

    /**
     * NavBar header code
     * 
     * @param string $handle classname fragment
     * @param string $brand  menu brand name
     * 
     * @return \Ease\Html\DivTag
     */
    public static function navBarHeader($handle, $brand)
    {
        $navstyle       = '.navbar-'.$handle.'-collapse';
        $nbhc['button'] = new \Ease\Html\ButtonTag([new \Ease\Html\Span(
                _('Switch navigation'), ['class' => 'sr-only']), new \Ease\Html\Span(
                null, ['class' => 'icon-bar']), new \Ease\Html\Span(
                null, ['class' => 'icon-bar']), new \Ease\Html\Span(
                null, ['class' => 'icon-bar'])],
            ['type' => 'button', 'class' => 'navbar-toggle', 'data-toggle' => 'collapse',
            'data-target' => $navstyle,]);
        if (strlen($brand)) {
            $nbhc['brand'] = new \Ease\Html\ATag('./', $brand,
                ['class' => 'navbar-brand']);
        }

        return new \Ease\Html\DivTag($nbhc, ['class' => 'navbar-header']);
    }

    /**
     * Přidá položku do navigační lišty.
     *
     * @param mixed  $Item         vkládaná položka
     * @param string $PageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return EasePage poiner to object well included
     */
    public function &addItem($Item, $PageItemName = null)
    {
        $added = $this->menuInnerContent->addItem($Item, $PageItemName);

        return $added;
    }

    /**
     * No Navbar menu contents 
     * 
     * @return array|mixed
     */
    public function emptyContents()
    {
        $this->menuInnerContent = null;
    }

    /**
     * Navbar menu contents
     * 
     * @return array|mixed
     */
    public function getContents()
    {
        return $this->menuInnerContent;
    }

    /**
     * Is NavBar empty ?
     *
     * @param Container $element Ease Html Element
     *
     * @return bool emptiness status
     */
    public function isEmpty($element = null)//: bool
    {
        return !count($this->menuInnerContent);
    }

    /**
     * Přidá položku menu.
     *
     * @param \Ease\Html\ATag $pageItem Položka menu
     * @param string          $pull     'right' strká položku v menu do prava
     *
     * @return EaseWebPage
     */
    public function &addMenuItem($pageItem, $pull = 'left')
    {
        if ($pull == 'left') {
            $menuItem = $this->nav->addItemSmart($pageItem);
        } else {
            $menuItem = $this->navRight->addItemSmart($pageItem);
        }
        if (isset($pageItem->tagProperties['href'])) {
            $href = basename($pageItem->tagProperties['href']);
            if (strstr($href, '?')) {
                list($targetPage) = explode('?', $href);
            } else {
                $targetPage = $href;
            }
            if ($targetPage == basename(\Ease\Page::phpSelf())) {
                if ($pull == 'left') {
                    $this->nav->lastItem()->setTagProperties(['class' => 'active']);
                } else {
                    $this->navRight->lastItem()->setTagProperties(['class' => 'active']);
                }
            }
        }

        return $menuItem;
    }

    /**
     * Add Dropdown Submenu.
     *
     * @param string $name
     * @param array  $items
     *
     * @return \Ease\Html\UlTag
     */
    public function &addDropDownSubmenu($name, $items)
    {
        $dropdown = $this->addItem(new \Ease\Html\UlTag(null,
                ['class' => 'dropdown-menu', 'role' => 'menu']));
        if (count($items)) {
            foreach ($items as $item) {
                $this->addMenuItem($item);
            }
        }

        return $dropdown;
    }

    /**
     * Vloží rozbalovací menu.
     *
     * @param string       $label popisek menu
     * @param array|string $items položky menu
     * @param string       $pull  směr zarovnání
     *
     * @return \Ease\Html\ULTag
     */
    public function &addDropDownMenu($label, $items, $pull = 'left')
    {
        Part::twBootstrapize();
        \Ease\Shared::webPage()->addJavaScript('$(\'.dropdown-toggle\').dropdown();',
            null, true);
        $dropDown     = new \Ease\Html\LiTag(null,
            ['class' => 'dropdown', 'id' => $label]);
        $dropDown->addItem(new \Ease\Html\ATag('#',
                $label.'<b class="caret"></b>',
                ['class' => 'dropdown-toggle', 'data-toggle' => 'dropdown']));
        $dropDownMenu = $dropDown->addItem(new \Ease\Html\UlTag(null,
                ['class' => 'dropdown-menu']));
        if (is_array($items)) {
            foreach ($items as $target => $label) {
                if (is_array($label)) {
                    //Submenu
                    $dropDownMenu->addItem($this->addDropDownSubmenu($target,
                            $label));
                } else {
                    //Item
                    if (!$target) {
                        $dropDownMenu->addItem(new \Ease\Html\LiTag(null,
                                ['class' => 'divider']));
                    } else {
                        $dropDownMenu->addItemSmart(new \Ease\Html\ATag($target,
                                $label));
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
