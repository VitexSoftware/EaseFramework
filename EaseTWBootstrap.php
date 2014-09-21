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
class EaseTWBPart extends EaseJQueryPart {

    /**
     * Vložení náležitostí pro twitter bootstrap
     */
    public function __construct() {
        parent::__construct();
        self::twBootstrapize();
    }

    /**
     * Opatří objekt vším potřebným pro funkci bootstrapu
     *
     */
    public static function twBootstrapize() {
        parent::jQueryze();
        $webPage = EaseShared::webPage();
        $webPage->includeJavaScript('twitter-bootstrap/js/bootstrap.js', 1, true);
        if (isset($webPage->mainStyle)) {
            $webPage->includeCss($webPage->mainStyle, true);
        }
//TODO: ONCE: $webPage->head->addItem('<meta name="viewport"
// content="width=device-width, initial-scale=1.0">');
        return true;
    }

    /**
     * Vrací ikonu
     *
     * @link  http://getbootstrap.com/components/#glyphicons Přehled ikon
     * @param string $code Kód ikony z přehledu
     */
    public static function GlyphIcon($code) {
        return '<span class="glyphicon glyphicon-' . $code . '"></span>';
    }

}

/**
 * Stránka TwitterBootstrap
 */
class EaseTWBWebPage extends EaseWebPage {

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
    public function __construct($pageTitle = null, &$userObject = null) {
        if (is_null($userObject)) {
            $userObject = EaseShared::user();
        }
        parent::__construct($pageTitle, $userObject);
        $this->includeCss($this->mainStyle, true);
        $this->head->addItem(
                '<meta name="viewport" content="width=device-width,initial-scale=1.0">'
        );
        $this->head->addItem('
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
    public function getStatusMessagesAsHtml($what = null) {
        /**
         * Session Singleton Problem hack
         */
//$this->easeShared->takeStatusMessages(EaseShared::user()->getStatusMessages(true));

        if (!count($this->easeShared->statusMessages)) {
            return '';
        }
        $htmlFargment = '';

        $allMessages = array();
        foreach ($this->easeShared->statusMessages as $quee => $messages) {
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

            if (is_object($this->logger)) {
                if (!isset($this->logger->logStyles[$messageType])) {
                    $messageType = 'notice';
                }
                if ($messageType == 'error') {
                    $messageType = 'danger';
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
class EaseTWBLinkButton extends EaseHtmlATag {

    /**
     * Odkazové tlačítko twbootstrabu
     *
     * @param string $href       cíl odkazu
     * @param mixed  $contents   obsah tlačítka
     * @param string $type       primary|info|success|warning|danger|inverse|link
     * @param array  $properties dodatečné vlastnosti
     */
    public function __construct($href, $contents = null, $type = null, $properties = null) {

        if (isset($properties['class'])) {
            $class = ' ' . $properties['class'];
        } else {
            $class = '';
        }
        if (is_null($type)) {
            $properties['class'] = 'btn btn-default';
        } else {
            $properties['class'] = 'btn btn-' . $type;
        }

        $properties['class'] .= $class;

        parent::__construct($href, $contents, $properties);
        EaseTWBPart::twBootstrapize();
    }

}

/**
 *
 */
class EaseTWSubmitButton extends EaseHtmlButtonTag {

    /**
     * Odesílací tlačítko formuláře Twitter Bootstrapu
     *
     * @param string $value vracená hodnota
     * @param string $type  primary|info|success|warning|danger|inverse|link
     */
    public function __construct($value = null, $type = null, $properties = null) {
        if (is_null($type)) {
            $properties['class'] = 'btn';
        } else {
            $properties['class'] = 'btn btn-' . $type;
        }
        parent::__construct($value, $properties);
        EaseTWBPart::twBootstrapize();
    }

}

/**
 *  NavBar
 */
class EaseTWBNavbar extends EaseHtmlDivTag {

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
    public function __construct($name = null, $brand = null, $properties = null) {
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
        $this->tagType = 'nav';
        $pullRigt = new EaseHtmlDivTag(NULL, null, array('class' => 'pull-right'));
        $this->navRight = $pullRigt->addItem(new EaseHtmlUlTag(null, array('class' => 'nav navbar-nav nav-right')));
        $navCollapse->addItem($pullRigt);
        EaseTWBPart::twBootstrapize();
    }

    public static function NavBarHeader($handle, $brand) {
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
    function & addItem($Item, $PageItemName = null) {
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
    function &addMenuItem($pageItem, $pull = 'left') {
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

    function & addDropDownSubmenu($name, $items) {
        $dropdown = $this->addItem(new EaseHtmlUlTag(null, array('class' => 'dropdown-menu', 'role' => 'menu')));

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
     * @return \EaseHtmlULTag
     */
    function & addDropDownMenu($label, $items, $pull = 'left') {
        EaseTWBPart::twBootstrapize();
        EaseShared::webPage()->addJavaScript('$(\'.dropdown-toggle\').dropdown();', null, true);
        $dropDown = new EaseHtmlLiTag(null, array('class' => 'dropdown', 'id' => $label));
        $dropDown->addItem(
                new EaseHtmlATag('#' . $label . '', $label . '<b class="caret"></b>', array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'))
        );
        $dropDownMenu = $dropDown->addItem(new EaseHtmlUlTag(null, array('class' => 'dropdown-menu')));
        if (is_array($items)) {
            foreach ($items as $target => $label) {
                if (is_array($label)) { //Submenu
                    $dropDownMenu->addItem($this->addDropDownSubmenu($target, $label));
                } else { //Item
                    if (!$target) {
                        $dropDownMenu->addItem(new EaseHtmlLiTag(null, array('class' => 'divider')));
                    } else {
                        $dropDownMenu->addItemSmart(new EaseHtmlATag($target, $label));
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

class EaseTWBForm extends EaseHtmlForm {

    public function __construct($formName, $formAction = null, $formMethod = 'post', $formContents = null, $tagProperties = null) {
        $tagProperties['class'] = 'form-horizontal';
        $tagProperties['role'] = 'form';
        parent::__construct($formName, $formAction, $formMethod, $formContents, $tagProperties);
    }

    /**
     *
     * @param type $input
     * @param type $caption
     */
    public function addInput($input, $caption = null) {
        $input->setTagId($this->getTagName());
        $controlGroup = $this->addItem(new EaseHtmlDivTag(null, null, array('class' => 'control-group')));
        $controlGroup->addItem(new EaseHtmlLabelTag($input->getTagID(), $caption, array('class' => 'control-label')));
        $controls = $controlGroup->addItem(new EaseHtmlDivTag('null', $input, array('class' => 'controls')));
    }

    /**
     * Vloží další element do formuláře a upraví mu css
     *
     * @param mixed  $pageItem     hodnota nebo EaseObjekt s metodou draw()
     * @param string $pageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return pointer Odkaz na vložený objekt
     */
    function &addItem($pageItem, $pageItemName = null) {
        if (is_object($pageItem) && method_exists($pageItem, 'setTagClass')) {
            if (strtolower($pageItem->tagType) == 'select') {
                $pageItem->setTagClass(trim(str_replace('form_control', '', $pageItem->getTagClass() . ' form-control')));
            }
        }
        return parent::addItem($pageItem, $pageItemName);
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
class EaseTWBFormGroup extends EaseHtmlDivTag {

    public function __construct($label = null, $content = null, $placeholder = null, $helptext = null) {
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
class EaseTWBStatusMessages extends EaseHtmlDivTag {

    /**
     * Blok stavových zpráv
     */
    public function __construct() {
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
    public function draw() {
        $StatusMessages = trim($this->webPage->getStatusMessagesAsHtml());
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
class EaseTWBTabs extends EaseContainer {

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
     * @param array  $partProperties
     */
    public function __construct($partName, $tabsList = null, $tagProperties = null) {
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
    function &addTab($tabName, $tabContent = '', $active = false) {
        $this->tabs[$tabName] = $tabContent;
        if ($active)
            $this->activeTab = $tabName;

        return $this->tabs[$tabName];
    }

    /**
     * Vložení skriptu a divů do stránky
     */
    public function finalize() {
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

class EaseTWGlyphIcon extends EaseHtmlSpanTag {

    /**
     * Vloží ikonu
     *
     * @link  http://getbootstrap.com/components/#glyphicons Přehled ikon
     * @param string $code Kód ikony z přehledu
     */
    public function __construct($code) {
        parent::__construct(null, null, array('class' => 'glyphicon glyphicon-' . $code));
    }

}

class EaseTWBButtonDropdown extends EaseHtmlDivTag {

    /**
     * Rozbalovací nabídka
     * @var EaseHtmlUlTag 
     */
    public $dropdown = null;

    /**
     *
     * @var type 
     */
    public $button = null;

    /**
     * Tlačítko s rozbalovacím menu
     * 
     * @param string $label popisek tlačítka
     * @param string $type  primary|info|success|warning|danger|inverse|link
     * @param string $size  lg = velký, sm = menší, xs = nejmenší
     * @param array  $items položky menu
     */
    function __construct($label = null, $type = 'default', $size = null, $items = null) {
        parent::__construct();
        $this->setTagClass('btn-group');
        $btnClass = 'btn btn-' . $type . ' ';
        if ($size) {
            $btnClass .= 'btn-' . $size;
        }
        $this->button = $this->addItem(new EaseHtmlButtonTag(array($label . ' <span class="caret"></span>'), array('class' => $btnClass . ' dropdown-toggle', 'type' => 'button', 'data-toggle' => 'dropdown')));

        $this->dropdown = $this->addItem(new EaseHtmlUlTag(null, array('class' => 'dropdown-menu', 'role' => 'menu')));

        if (count($items)) {
            foreach ($items as $item) {
                $this->addMenuItem($item);
            }
        }
    }

    /**
     * Vloží položku do menu tlačítka
     * 
     * @param type $pageItem
     * @return EaseHtmlLiTag
     */
    function addMenuItem($pageItem) {
        return $this->dropdown->addItemSmart($pageItem);
    }

}

class EaseTWBCheckBoxGroup extends EaseContainer {

    function __construct($param) {
        
    }

}

class EaseTWRadioButtonGroup extends EaseContainer {

    /**
     * Jméno
     * @var string 
     */
    public $name = null;

    /**
     * Typ 
     * @var bool 
     */
    public $inline = false;

    /**
     * Položky k zobrazení
     * @var array 
     */
    public $radios = array();

    /**
     * Předvolená hodnota
     * @var string 
     */
    public $checked = null;

    /**
     * Zobrazí pole radiobuttonů
     * 
     * @param string $name
     * @param array  $radios pole Hodnota=>Popisek
     * @param string $checked
     * @param boolean $inline 
     */
    function __construct($name, $radios, $checked = null, $inline = false) {
        $this->name = $name;
        $this->checked = $checked;
        $this->inline = $inline;
        $this->radios = $radios;
        parent::__construct();
    }

    /**
     * Seskládá pole radiobuttonů
     */
    function finalize() {
        $class = 'radio';
        if ($this->inline) {
            $class .= '-inline';
        }
        $pos = 1;
        foreach ($this->radios as $value => $caption) {
            if ($value == $this->checked) {
                $checked = 'checked';
            } else {
                $checked = null;
            }

            $tagProperties = array(
                'id' => $this->name . $pos++,
                'name' => $this->name,
                $checked
            );

            $this->addItem(
                    new EaseHtmlDivTag(
                    null, new EaseHtmlLabelTag(
                    null, array(
                new EaseHtmlInputRadioTag($this->name, $value, $tagProperties),
                $caption
                    )
                    ), array('class' => $class)
                    )
            );
        }
    }

}

class EaseTWModal extends EaseContainer {

    function __construct($name, $content = null, $properties) {
        parent::__construct();

        EaseShared::webPage()->addItem('
<!-- Modal -->
<div class="modal fade" id="' . $name . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">' . $name . '</h4>
      </div>
      <div class="modal-body">
        ' . $content . '
      </div>
      <div class="modal-footer">
        <button id="' . $name . 'ko" type="button" class="btn btn-default" data-dismiss="modal">' . _('Zavřít') . '</button>
        <button id="' . $name . 'ok" type="button" class="btn btn-primary">' . _('Uložit') . '</button>
      </div>
    </div>
  </div>
</div>
');

        EaseShared::webPage()->addJavaScript(' $(function ()    
{ 
    $("#' . $name . '").modal( {' . EaseTWBPart::partPropertiesToString($properties) . '});    
}); 
', null, true);
    }

}

/**
 * Twitter Bootrstap Well 
 */
class EaseTWBWell extends EaseHtmlDivTag {

    /**
     * Twitter Bootrstap Well 
     * 
     * @param mixed $content
     */
    public function __construct($content = null) {
        parent::__construct(null, $content, array('class' => 'well'));
    }

}

/**
 * Twitter Bootrstap Container
 */
class EaseTWBContainer extends EaseHtmlDivTag {

    /**
     * Twitter Bootrstap Container
     * 
     * @param mixed $content
     */
    public function __construct($content = null) {
        parent::__construct(null, $content, array('class' => 'container'));
    }

}

/**
 * Twitter Bootrstap Row
 */
class EaseTWBRow extends EaseHtmlDivTag {

    /**
     * Twitter Bootrstap Row
     * 
     * @param mixed $content
     */
    public function __construct($content = null) {
        parent::__construct(null, $content, array('class' => 'row'));
    }

}

class EaseTWBPanel extends EaseHtmlDivTag {

    /**
     * Hlavička panelu
     * @var EaseHtmlDivTag 
     */
    public $heading = null;
    /**
     * Tělo panelu
     * @var EaseHtmlDivTag 
     */
    public $body = null;
    /**
     * Patička panelu
     * @var EaseHtmlDivTag 
     */
    public $footer = null;

    /**
     * Panel Twitter Bootstrapu
     * 
     * @param type $heading
     * @param type $type
     * @param type $body
     * @param type $footer
     */
    function __construct($heading, $type = 'default', $body = null, $footer = null) {
        parent::__construct($name, null, array('class' => 'panel panel-' . $type));
        $this->heading = new EaseHtmlDivTag(null, $heading, array('class' => 'panel-heading'));
        $this->body = $this->addItem(new EaseHtmlDivTag(null, $body, array('class' => 'panel-body')));
        if ($footer) {
            $this->footer = $this->addItem(new EaseHtmlDivTag(null, $footer, array('class' => 'panel-footer')));
        }
    }
    
    /**
     * Vloží další element do objektu
     *
     * @param mixed  $pageItem     hodnota nebo EaseObjekt s metodou draw()
     * @param string $pageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return pointer Odkaz na vložený objekt
     */
    function addItem($pageItem, $pageItemName = null) {
        $this->body->addItem($pageItem, $pageItemName);
    }

}
