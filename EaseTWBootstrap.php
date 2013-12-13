<?php

/**
 * Pro pohodlnou práci s twitter bootstrap
 * běžně používané prvky UI
 *
 * @package    EaseFrameWork
 * @subpackage EaseHtml
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2012 Vitex@vitexsoftware.cz (G)
 * @link       http://twitter.github.com/bootstrap/index.html
 */
require_once 'EaseWebPage.php';
require_once 'EaseJQuery.php';
require_once 'EaseHtmlForm.php';

/**
 * jQuery UI common class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseTWBPart extends EaseJQueryPart
{

    /**
     * Vložení náležitostí pro twitter bootstrap
     */
    public function __construct()
    {
        parent::__construct();
        self::twBootstrapize();
    }

    /**
     * Opatří objekt vším potřebným pro funkci bootstrapu
     *
     */
    public static function twBootstrapize()
    {
        parent::jQueryze();
        $webPage = EaseShared::webPage();
        $webPage->includeJavaScript('twitter-bootstrap/bootstrap.js', 1, true);
        if (isset($webPage->mainStyle)) {
            $webPage->includeCss($webPage->mainStyle, true);
        }
        //TODO: ONCE: $webPage->Head->addItem('<meta name="viewport"
        // content="width=device-width, initial-scale=1.0">');
        return true;
    }

    /**
     * Vrací ikonu
     *
     * @link  http://getbootstrap.com/components/#glyphicons Přehled ikon
     * @param string $code Kód ikony z přehledu
     */
    public static function GlyphIcon($code)
    {
        return '<span class="glyphicon glyphicon-' . $code . '"></span>';
    }

}

/**
 * Stránka TwitterBootstrap
 */
class EaseTWBWebPage extends EaseWebPage
{

    /**
     * CSSKo bootstrapu
     * @var string url
     */
    public $mainStyle = 'twitter-bootstrap/css/bootstrap.css';

    /**
     * Stránka s podporou pro twitter bootstrap
     *
     * @param type $pageTitle
     * @param type $userObject
     */
    public function __construct($pageTitle = null, &$userObject = null)
    {
        if (is_null($userObject)) {
            $userObject = EaseShared::user();
        }
        parent::__construct($pageTitle, $userObject);
        $this->includeCss($this->mainStyle, true);
        $this->Head->addItem(
                '<meta name="viewport" content="width=device-width,initial-scale=1.0">'
        );
        $this->Head->addItem('
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="../../assets/js/html5shiv.js"></script>
      <script src="../../assets/js/respond.min.js"></script>
    <![endif]-->');
    }

    /**
     * Vrací zprávy uživatele
     *
     * @param string $what info|warning|error|success
     *
     * @return string
     */
    public function getStatusMessagesAsHtml($what = null)
    {
        /**
         * Session Singleton Problem hack
         */
        //$this->EaseShared->takeStatusMessages(EaseShared::user()->getStatusMessages(true));

        if (!count($this->EaseShared->StatusMessages)) {
            return '';
        }
        $htmlFargment = '';

        $allMessages = array();
        foreach ($this->EaseShared->StatusMessages as $quee => $messages) {
            foreach ($messages as $MesgID => $message) {
                $allMessages[$MesgID][$quee] = $message;
            }
        }
        ksort($allMessages);
        foreach ($allMessages as $message) {
            $messageType = key($message);

            if (is_array($what)) {
                if (!in_array($messageType, $what)) {
                    continue;
                }
            }

            $message = reset($message);

            if (is_object($this->Logger)) {
                if (!isset($this->Logger->LogStyles[$messageType])) {
                    $messageType = 'notice';
                }
                $htmlFargment .= '<div class="alert alert-' . $messageType . '" >' . $message . '</div>' . "\n";
            } else {
                $htmlFargment .= '<div class="alert">' . $message . '</div>' . "\n";
            }
        }

        return $htmlFargment;
    }

}

/**
 * Odkazové tlačítko twbootstrabu
 *
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2012 Vitex@vitexsoftware.cz (G)
 * @link       http://twitter.github.com/bootstrap/base-css.html#buttons Buttons
 */
class EaseTWBLinkButton extends EaseHtmlATag
{

    /**
     * Odkazové tlačítko twbootstrabu
     *
     * @param string $href       cíl odkazu
     * @param mixed  $contents   obsah tlačítka
     * @param string $type       primary|info|success|warning|danger|inverse|link
     * @param array  $Properties dodatečné vlastnosti
     */
    public function __construct($href, $contents = null, $type = null, $Properties = null)
    {
        if (is_null($type)) {
            $Properties['class'] = 'btn btn-default';
        } else {
            $Properties['class'] = 'btn btn-' . $type;
        }
        parent::__construct($href, $contents, $Properties);
        EaseTWBPart::twBootstrapize();
    }

}

/**
 *
 */
class EaseTWSubmitButton extends EaseHtmlButtonTag
{

    /**
     * Odesílací tlačítko formuláře Twitter Bootstrapu
     *
     * @param string $Value vracená hodnota
     * @param string $type  primary|info|success|warning|danger|inverse|link
     */
    public function __construct($Value = null, $type = null, $properties = null)
    {
        if (is_null($type)) {
            $properties['class'] = 'btn';
        } else {
            $properties['class'] = 'btn btn-' . $type;
        }
        parent::__construct($Value, $properties);
        EaseTWBPart::twBootstrapize();
    }

}

/**
 *  NavBar
 */
class EaseTWBNavbar extends EaseHtmlDivTag
{

    /**
     * Vnitřek menu
     * @var EaseHtmlDivTag
     */
    public $menuInnerContent = null;

    /**
     * Položky menu
     * @var EaseHtmlUlTag
     */
    private $nav;

    /**
     * Položky menu přidávané vpravo
     * @var EaseHtmlUlTag
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
        parent::__construct($name, null, $properties);
        $this->menuInnerContent = parent::addItem(new EaseHtmlDivTag(null, null, array('class' => 'navbar-inner')));

        $this->addItem(self::NavBarHeader($name, $brand));

        $navCollapse = $this->addItem(new EaseHtmlDivTag(null, null, array('class' => 'collapse navbar-collapse navbar-' . $name . '-collapse')));
        $this->nav = $navCollapse->addItem(new EaseHtmlUlTag(null, array('class' => 'nav navbar-nav')));
        $this->TagType = 'nav';
        $pullRigt = new EaseHtmlDivTag(NULL, null, array('class' => 'pull-right'));
        $this->navRight = $pullRigt->addItem(new EaseHtmlUlTag(null, array('class' => 'nav navbar-nav nav-right')));
        $navCollapse->addItem($pullRigt);
        EaseTWBPart::twBootstrapize();
    }

    public static function NavBarHeader($handle, $brand)
    {
        $navstyle = '.navbar-' . $handle . '-collapse';
        $nbhc['button'] = new EaseHtmlButtonTag(array(
            new EaseHtmlSpanTag(null, _('přepnutí navigace'), array('class' => 'sr-only')),
            new EaseHtmlSpanTag(null, null, array('class' => 'icon-bar')),
            new EaseHtmlSpanTag(null, null, array('class' => 'icon-bar')),
            new EaseHtmlSpanTag(null, null, array('class' => 'icon-bar'))
                ), array('type' => 'button', 'class' => 'navbar-toggle', 'data-toggle' => 'collapse', 'data-target' => $navstyle));

        if ($brand) {
            $nbhc['brand'] = new EaseHtmlATag('./', $brand, array('class' => 'navbar-brand'));
        }

        return new EaseHtmlDivTag(null, $nbhc, array('class' => 'navbar-header'));
    }

    /**
     * Přidá položku do navigační lišty
     *
     * @param mixed  $Item         vkládaná položka
     * @param string $PageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return EasePage poiner to object well included
     */
    function & addItem($Item, $PageItemName = null)
    {
        return $this->menuInnerContent->addItem($Item, $PageItemName);
    }

    /**
     * Přidá položku menu
     *
     * @param EaseHtmlATag $pageItem Položka menu
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
        if (isset($pageItem->TagProperties['href'])) {
            $href = basename($pageItem->TagProperties['href']);
            if (strstr($href, '?')) {
                list($targetPage, $params) = explode('?', $href);
            } else {
                $targetPage = $href;
            }
            if ($targetPage == basename(EasePage::phpSelf())) {
                if ($pull == 'left') {
                    $this->nav->lastItem()->setTagProperties(array('class' => 'active'));
                } else {
                    $this->navRight->lastItem()->setTagProperties(array('class' => 'active'));
                }
            }
        }

        return $menuItem;
    }

    /**
     * Vloží rozbalovací menu
     *
     * @param  string         $label popisek menu
     * @param  array|string   $items položky menu
     * @param  string         $pull  směr zarovnání
     * @return \EaseHtmlULTag
     */
    function & addDropDownMenu($label, $items, $pull = 'left')
    {
        EaseTWBPart::twBootstrapize();
        EaseShared::webPage()->addJavaScript('$(\'.dropdown-toggle\').dropdown();', null, true);
        $dropDown = new EaseHtmlLiTag(null, array('class' => 'dropdown', 'id' => $label));
        $dropDown->addItem(
                new EaseHtmlATag('#' . $label . '', $label . '<b class="caret"></b>', array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'))
        );
        $dropDownMenu = $dropDown->addItem(new EaseHtmlUlTag(null, array('class' => 'dropdown-menu')));
        if (is_array($items)) {
            foreach ($items as $target => $label) {
                $dropDownMenu->addItemSmart(new EaseHtmlATag($target, $label));
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

class EaseTWBForm extends EaseHtmlForm
{

    public function __construct($FormName, $FormAction = null, $FormMethod = 'post', $FormContents = null, $tagProperties = null)
    {
        $tagProperties['class'] = 'form-horizontal';
        $tagProperties['role'] = 'form';
        parent::__construct($FormName, $FormAction, $FormMethod, $FormContents, $tagProperties);
    }

    /**
     *
     * @param type $input
     * @param type $caption
     */
    public function addInput($input, $caption = null)
    {
        $input->setTagId($this->getTagName());
        $controlGroup = $this->addItem(new EaseHtmlDivTag(null, null, array('class' => 'control-group')));
        $controlGroup->addItem(new EaseHtmlLabelTag($input->getTagID(), $caption, array('class' => 'control-label')));
        $controls = $controlGroup->addItem(new EaseHtmlDivTag('null', $input, array('class' => 'controls')));
    }

}

/**
 * Položka TWBootstrp formuláře
 *
 * @param string      $label       popisek pole formuláře
 * @param EaseHtmlTag $content     widget formuláře
 * @param string      $placeholder předvysvětlující text
 * @param string      $helptext    Nápvěda pod prvkem
 */
class EaseTWBFormGroup extends EaseHtmlDivTag
{

    public function __construct($label = null, $content = null, $placeholder = null, $helptext = null)
    {
        $formKey = self::lettersOnly($label);

        $properties['class'] = 'form-group';
        parent::__construct(null, null, $properties);
        $this->addItem(new EaseHtmlLabelTag($formKey, $label));
        $content->setTagClass('form-control');
        if ($placeholder) {
            $content->SetTagProperties(array('placeholder' => $placeholder));
        }
        $content->setTagId($formKey);

        $this->addItem($content);
        if ($helptext) {
            $this->addItem(new EaseHtmlPTag($helptext, array('class' => 'help-block')));
        }
    }

}

/**
 * Vypisuje stavové hlášky
 */
class EaseTWBStatusMessages extends EaseHtmlDivTag
{

    /**
     * Blok stavových zpráv
     */
    public function __construct()
    {
        $properties['class'] = 'well';
        $properties['id'] = 'StatusMessages';
        $properties['title'] = _('kliknutím skryjete zprávy');
        $properties['style'] = 'padding-top: 40px; padding-bottom: 0px;';
        parent::__construct(null, null, $properties);
        EaseJQueryPart::jQueryze();
        $this->addJavaScript('$("#StatusMessages").click(function () { $("#StatusMessages").fadeTo("slow",0.25).slideUp("slow"); });', 3, true);
    }

    /**
     * Vypíše stavové zprávy
     */
    public function draw()
    {
        $StatusMessages = trim($this->WebPage->getStatusMessagesAsHtml());
        if ($StatusMessages) {
            parent::addItem($StatusMessages);
            parent::draw();
        } else {
            $this->suicide();
        }
    }

}

/**
 * Create TWBootstrap tabs
 *
 * @see http://getbootstrap.com/2.3.2/components.html#navs
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseTWBTabs extends EaseContainer
{

    public $partName = 'TWBTabs';

    /**
     * Array of tab names=>contents
     * @var array
     */
    public $tabs = array();

    /**
     * Jméno aktivního tabu
     * @var string
     */
    private $activeTab = null;

    /**
     * Create TWBootstrap tabs
     *
     * @param string $partName       - DIV id
     * @param array  $tabsList
     * @param array  $PartProperties
     */
    public function __construct($partName, $tabsList = null, $tagProperties = null)
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
     * Vytvoří nový tab a vloží do něj obsah
     *
     * @param string  $tabName    jméno a titulek tabu
     * @param mixed   $tabContent
     * @param boolean $active     Má být tento tab aktivní ?
     *
     * @return pointer odkaz na vložený obsah
     */
    function &addTab($tabName, $tabContent = '', $active = false)
    {
        $this->tabs[$tabName] = $tabContent;
        if ($active)
            $this->activeTab = $tabName;

        return $this->tabs[$tabName];
    }

    /**
     * Vložení skriptu a divů do stránky
     */
    public function finalize()
    {
        if (is_null($this->activeTab)) {
            $this->activeTab = current(array_keys($this->tabs));
        }
        $tabsUl = $this->addItem(new EaseHtmlUlTag(null, array('class' => 'nav nav-tabs', 'id' => $this->partName)));
        foreach ($this->tabs as $tabName => $tabContent) {
            if ($tabName == $this->activeTab) {
                $tabsUl->addItem(new EaseHtmlLiTag(new EaseHtmlATag('#' . self::lettersOnly($tabName), $tabName, array('data-toggle' => 'tab')), array('class' => 'active')));
            } else {
                $tabsUl->addItem(new EaseHtmlLiTag(new EaseHtmlATag('#' . self::lettersOnly($tabName), $tabName, array('data-toggle' => 'tab'))));
            }
        }

        $tabDiv = $this->addItem(new EaseHtmlDivTag($this->partName . 'body', null, array('class' => 'tab-content')));
        foreach ($this->tabs as $tabName => $tabContent) {
            if ($tabName == $this->activeTab) {
                $tabDiv->addItem(new EaseHtmlDivTag(self::lettersOnly($tabName), $tabContent, array('class' => 'tab-pane active')));
            } else {
                $tabDiv->addItem(new EaseHtmlDivTag(self::lettersOnly($tabName), $tabContent, array('class' => 'tab-pane')));
            }
        }

        EaseTWBPart::twBootstrapize();
        EaseShared::webPage()->addJavaScript("
        $('#" . $this->partName . " a[href=\"#" . self::lettersOnly($this->activeTab) . "\"]').tab('show');
", NULL, true);
    }

}

class EaseTWGlyphIcon extends EaseHtmlSpanTag
{

    /**
     * Vloží ikonu
     *
     * @link  http://getbootstrap.com/components/#glyphicons Přehled ikon
     * @param string $code Kód ikony z přehledu
     */
    public function __construct($code)
    {
        parent::__construct(null, null, array('class' => 'glyphicon glyphicon-' . $code));
    }

}
