<?php

/**
 * Pro pohodlnou práci s jQuery widgety, nabízí EaseFrameWork třídy pro všechny
 * běžně používané prvky UI
 *
 * @package    EaseFrameWork
 * @subpackage EaseHtml
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2011-2012 Vitex@vitexsoftware.cz (G)
 * @link       http://jqueryui.com/demos/
 */
require_once 'EaseJQuery.php';
require_once 'EaseHtmlForm.php';

/**
 * Slider
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @see http://docs.jquery.com/UI/Slider
 */
class EaseJQuerySlider extends EaseJQueryUIPart
{

    /**
     * Class used to create form input
     * @var type
     */
    public $InputClass = 'EaseHtmlInputHiddenTag';

    /**
     * Additional JS code to solve show slider values
     * @var type
     */
    public $SliderAdd = '';

    /**
     * Jquery Slider
     *
     * @param string $Name
     * @param int    $Value can be array for multislider
     */
    public function __construct($Name, $Value = null)
    {
        $this->PartName = $Name;
        parent::__construct();
        if (!is_null($Value)) {
            $this->setValue($Value);
        }
    }

    /**
     * Nastavuje jméno objektu
     * Je li znnámý, doplní jméno objektu jménem inputu
     *
     * @param string $ObjectName vynucené jméno objektu
     *
     * @return string new name
     */
    public function setObjectName($ObjectName = null)
    {
        if ($ObjectName) {
            return parent::setObjectName($ObjectName);
        } else {
            if ($this->PartName) {
                return parent::setObjectName(get_class($this) . '@' . $this->PartName);
            } else {
                return parent::setObjectName();
            }
        }
    }

    /**
     * Setup input field/s value/s
     *
     * @param string $Value
     */
    public function setValue($Value)
    {
        if (is_array($Value)) {
            $this->setPartProperties(array('values' => $Value));
        } else {
            $this->setPartProperties(array('value' => $Value));
        }
    }

    /**
     * Nastaví více hodnot
     *
     * @param darray $Data hodnoty k přednastavení
     */
    public function setValues($Data)
    {
        if (isset($this->PartProperties['values'])) {
            $NewValues = array();
            foreach (array_keys($this->PartProperties['values']) as $Offset => $ID) {
                if (isset($Data[$ID])) {
                    $this->PageParts[$this->InputClass . '@' . $ID]->setValue($Data[$ID]);
                    $NewValues[$ID] = $Data[$ID];
                }
            }
            if (count($NewValues)) {
                $this->setValue($NewValues);
            }
        }
    }

    /**
     * Return assigned form input Tag name
     *
     * @return string
     */
    public function getTagName()
    {
        return $this->PartName;
    }

    /**
     * Javascriptvový kod slideru
     *
     * @return string
     */
    public function onDocumentReady()
    {
        $JavaScript = '$("#' . $this->PartName . '-slider").slider( { ' . $this->getPartPropertiesToString() . ' } );';
        if (isset($this->PartProperties['values'])) {
            foreach (array_keys($this->PartProperties['values']) as $Offset => $ID) {
                $JavaScript .= "\n" . '$( "#' . $ID . '" ).val( $( "#' . $this->PartName . '-slider" ).slider( "values", ' . $Offset . ' ) );';
            }
        } else {
            $JavaScript .= "\n" . '$( "#' . $this->PartName . '" ).val( $( "#' . $this->PartName . '-slider" ).slider( "value" ) );';
        }

        return $JavaScript;
    }

    /**
     * Naplnění hodnotami
     */
    public function afterAdd()
    {
        if (isset($this->PartProperties['values'])) {
            if (is_array($this->PartProperties['values'])) {
                foreach ($this->PartProperties['values'] as $ValueID => $Value) {
                    $this->addItem(new $this->InputClass($ValueID, $Value));
                    $this->LastItem->setTagID($ValueID);
                }
            }
        } else {
            $this->addItem(new $this->InputClass($this->PartName, $this->PartProperties['value']));
            $this->LastItem->setTagID($this->PartName);
        }
    }

    /**
     * Vložení skriptů do schránky
     */
    public function finalize()
    {
        EaseShared::WebPage()->addCSS(' #' . $this->PartName . ' { margin: 10px; }');
        $this->addItem(new EaseHtmlDivTag($this->PartName . '-slider'));
        if (isset($this->PartProperties['values'])) {
            if (is_array($this->PartProperties['values'])) {
                $JavaScript = '';
                foreach (array_keys($this->PartProperties['values']) as $Offset => $ID) {
                    $JavaScript .= ' $( "#' . $ID . '" ).val( ui.values[' . $Offset . '] );';
                }
                $this->setPartProperties(array('slide' => 'function (event, ui) { ' . $JavaScript . $this->SliderAdd . ' }'));
            }
        } else {
            $this->setPartProperties(array('slide' => 'function (event, ui) { $( "#' . $this->PartName . '" ).val( ui.value ); ' . $this->SliderAdd . ' }'));
        }
        if (!isset($this->PartProperties['value'])) {
            $this->PartProperties['value'] = null;
        }
        $this->setPartProperties(array(
            'change' => 'function (event, ui) {
            $("#' . $this->PartName . '-slider a").html( ui.value ); }',
            'create' => 'function (event, ui) { $("#' . $this->PartName . '-slider a").html( ' . $this->PartProperties['value'] . ' ).css("text-align", "center"); }  ')
        );

        EaseShared::WebPage()->addJavaScript(';', null, true);

        return parent::finalize();
    }

}

/**
 * Toggle checboxes within
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseCheckboxToggler extends EaseHtmlDivTag
{

    public function finalize()
    {
        EaseJQueryPart::jQueryze($this);
        $this->addItem('<input class="button" value="☑ Označit vše" type="button" name="checkAllAuto" onClick="jQuery(\'#' . $this->getTagID() . ' :checkbox:not(:checked)\').attr(\'checked\', \'checked\');" id="checkAllAuto"/>');
        $this->addItem('<input class="button" value="☐ Odznačit vše" type="button" name="checkAllAuto" onClick="jQuery(\'#' . $this->getTagID() . ' :checkbox:checked\').removeAttr(\'checked\', \'checked\');" id="checkAllAuto"/>');
    }

}

/**
 * TinyMce komponenta
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @todo add include files
 */
class EaseTinyMCE extends EaseHtmlTextareaTag
{

    /**
     * Vložení těla sktiptu
     */
    public function afterAdd()
    {
        $this->setTagID($this->getTagName());
        $this->WebPage->includeJavaScript('includes/javascript/tiny_mce/tiny_mce.js');
        $this->WebPage->addJavaScript('
tinyMCE.init({
mode : "textareas",
theme : "simple"
});
'
        );
    }

}

/**
 * Color picker Part
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseJQColorPicker extends EaseHtmlInputTextTag
{

    public function finalize()
    {
        $this->setTagID($this->getTagName());
        $this->WebPage->includeJavaScript('jquery.js', 0);
        $this->WebPage->includeJavaScript('colorpicker.js');
        $this->WebPage->includeCss('colorpicker.css');
        $this->WebPage->addJavaScript(
                "$(document).ready(function () {
    $('#" . $this->getTagID() . "').ColorPicker({
    onSubmit: function (hsb, hex, rgb, el) {
        $(el).val(hex);
        $(el).ColorPickerHide();
    },
    onBeforeShow: function () {
        $(this).ColorPickerSetColor(this.value);
    }
    })
    .bind('keyup', function () {
    $(this).ColorPickerSetColor(this.value);
    });

 });
", 3);
    }

}

/**
 * Create jQueryUI tabs
 *
 * @see http://jqueryui.com/demos/tabs/
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseJQueryUITabs extends EaseJQueryUIPart
{

    /**
     * Array of tab names=>contents
     * @var array
     */
    public $Tabs = array();

    /**
     * Create jQueryUI tabs
     *
     * @param string $PartName       - DIV id
     * @param array  $TabsList
     * @param array  $PartProperties
     */
    public function __construct($PartName, $TabsList = null, $PartProperties = null)
    {
        $this->setPartName($PartName);
        parent::__construct();
        if (is_array($TabsList)) {
            $this->Tabs = array_merge($this->Tabs, $TabsList);
        }
        if (!is_null($PartProperties)) {
            $this->setPartProperties($PartProperties);
        }
    }

    /**
     * Vytvoří nový tab a vloží do něj obsah
     *
     * @param string $TabName    jméno a titulek tabu
     * @param mixed  $TabContent
     *
     * @return pointer odkaz na vložený obsah
     */
    function &addTab($TabName, $TabContent = '')
    {
        $this->Tabs[$TabName] = $TabContent;

        return $this->Tabs[$TabName];
    }

    /**
     * Add dynamicaly loaded content
     *
     * @param string $TabName
     * @param string $Url
     */
    public function addAjaxTab($TabName, $Url)
    {
        $this->Tabs[$TabName] = 'url:' . $Url;
    }

    /**
     * Vložení skriptu a divů do stránky
     */
    public function finalize()
    {
        $this->addJavaScript('$(function () { $( "#' . $this->PartName . '" ).tabs( {' . $this->getPartPropertiesToString() . '} ); });',null,true);
        $Div = $this->addItem(new EaseHtmlDivTag($this->PartName));
        $UlTag = $Div->addItem(new EaseHtmlUlTag());
        $Index = 0;
        foreach ($this->Tabs as $TabName => $TabContent) {
            if (!strlen($TabContent) || substr_compare($TabContent, 'url:', 0, 4)) {
                $UlTag->addItem(new EaseHtmlATag('#' . $this->PartName . '-' . ++$Index, $TabName));
                $Div->addItem(new EaseHtmlDivTag($this->PartName . '-' . $Index));
                $Div->addToLastItem($TabContent);
            } else {
                $UlTag->addItem(new EaseHtmlATag(str_replace('url:', '', $TabContent), $TabName));
                $Div->addItem(new EaseHtmlDivTag($this->PartName . '-' . $Index));
            }
        }

        self::jQueryze($this);
    }

}

/**
 * InPlace Editor part
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseInPlaceEditor extends EaseJQueryUIPart
{

    /**
     * Políčko editoru
     * @var EaseHtmlInputTextTag
     */
    public $EditorField = null;

    /**
     * Script zpracovávající odeslaná data
     * @var string
     */
    public $SubmitTo = null;

    /**
     * Inplace Editor
     *
     * @param name   $Name
     * @param string $Content
     * @param string $SubmitTo
     * @param array  $Properties
     */
    public function __construct($Name, $Content, $SubmitTo = null, $Properties = null)
    {
        parent::__construct($Name, $Content, $Properties);
        if (!$SubmitTo) {
            $this->SubmitTo = str_replace('.php', 'Ajax.php', $_SERVER['PHP_SELF']);
        } else {
            $this->SubmitTo = $SubmitTo;
        }
        $this->EditorField = $this->addItem(new EaseHtmlInputTextTag($Name, $Content, $Properties));
        $this->EditorField->setTagID();
    }

    /**
     * Vložení javascriptů
     */
    public function finalize()
    {

        $this->includeJavaScript('jquery-editinplace.js', 2, true);
    }

    /**
     * Vykreslení
     */
    public function draw()
    {
        parent::draw();
        $JavaScript = new EaseJavaScript('$("#' . $this->EditorField->getTagID() . '").editInPlace({ url: "' . $this->SubmitTo . '", show_buttons: true }); ');
        $JavaScript->draw();
    }

}

/**
 * Click to frameset title to collapse in line
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @filesource http://michael.theirwinfamily.net/demo/jquery/collapsible-fieldset/index.html
 */
class EaseHtmlFieldSetCollapsable extends EaseHtmlFieldSet
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
        EaseShared::WebPage()->includeJavaScript('collapsible.js', 4, true);
        EaseShared::WebPage()->includeCss('collapsible.css', true);
        EaseShared::WebPage()->addJavaScript('$(\'#' . $this->getTagID() . '\').collapse({ closed: ' . (($this->Closed) ? 'true' : 'false') . ' });', null, true);
    }

}

/*
  $this->WebPage->IncludeJavaScript('http://jqueryui.com/themeroller/themeswitchertool/');
  $this->WebPage->addJavaScript('$(\'#switcher\').themeswitcher();',null,true);
  $this->addItem(new EaseHtmlDivTag('switcher'));
 */

/**
  class EaseSimpleScrollerPart extends EaseJQueryUIPart
  {
  }
 */

/**
 * Vstupní prvek pro soubor
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseAjaxFileInput extends EaseHtmlInputFileTag
{

    public $UploadTarget = '';
    public $UploadDoneCode = '$(res).insertAfter(this);';

    /**
     * Komponenta pro Upload souboru
     *
     * @param string $Name
     * @param string $UploadTarget
     * @param string $Value
     */
    public function __construct($Name, $UploadTarget, $Value = null)
    {
        $this->UploadTarget = $UploadTarget;
        parent::__construct($Name, $Value);
        $this->setTagID($Name);
    }

    public function finalize()
    {

        $this->includeJavaScript('jquery.js', 0, true);
        $this->includeJavaScript('jquery.upload.js', 4, true);
        $this->addJavaScript('
 $(\'#' . $this->getTagID() . '\').change(function () {
    $(this).upload(\'' . $this->UploadTarget . '\', function (res) {
        ' . $this->UploadDoneCode . '
    }, \'html\');
});
', null, true);
    }

    public function setUpDoneCode($DoneCode)
    {
        $this->UploadDoneCode = $DoneCode;
    }

}

/**
 * Hypertextový odkaz v designu jQueryUI tlačítka
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @link http://jqueryui.com/demos/button/
 */
class EaseJQueryLinkButton extends EaseJQueryUIPart
{

    /**
     * Jméno tlačítka
     * @var string
     */
    private $Name = null;

    /**
     * Paramatry pro jQuery .button()
     * @var array
     */
    public $JQOptions = null;

    /**
     * Odkaz tlačítka
     * @var EaseHtmlATag
     */
    public $Button = NULL;

    /**
     * Link se vzhledem tlačítka
     *
     * @see http://jqueryui.com/demos/button/
     *
     * @param string       $Href       cíl odkazu
     * @param string       $Contents   obsah tlačítka
     * @param array|string $JQOptions  parametry pro $.button()
     * @param array        $Properties vlastnosti HTML tagu
     */
    public function __construct($Href, $Contents, $JQOptions = null, $Properties = null)
    {
        parent::__construct();
        if (!isset($Properties['id'])) {
            $this->Name = EaseBrick::randomString();
        } else {
            $this->Name = $Properties['id'];
        }
        $this->JQOptions = $JQOptions;
        $this->Button = $this->addItem(new EaseHtmlATag($Href, $Contents));
        if ($Properties) {
            $this->Button->setTagProperties($Properties);
        }
        $this->Button->setTagProperties(array('id' => $this->Name));
    }

    /**
     * Nastaveni javascriptu
     */
    public function onDocumentReady()
    {
        return '$("#' . $this->Name . '").button( {' . EaseJQueryPart::partPropertiesToString($this->JQOptions) . '} )';
    }

    /**
     * Nastaví ID linku tlačítka
     *
     * @param  type $TagID ID tagu
     * @return type
     */
    public function setTagID($TagID = NULL)
    {
        return $this->Button->setTagID($TagID);
    }

    /**
     * Vrací ID linku tlačítka
     *
     * @return type
     */
    public function getTagID()
    {
        return $this->Button->getTagID();
    }

}

/**
 * Odesílací tlačítko
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @link http://jqueryui.com/demos/button/
 */
class EaseJQuerySubmitButton extends EaseJQueryUIPart
{

    /**
     * Jméno tlačítka
     * @var string
     */
    public $Name = null;

    /**
     * Paramatry pro jQuery .button()
     * @var array
     */
    public $JQOptions = null;

    /**
     * Odkaz na objekt tlačítka
     * @var EaseHtmlInputSubmitTag
     */
    public $Button = null;

    /**
     * Odesílací tlačítko
     *
     * @see http://jqueryui.com/demos/button/
     * @param string       $Name
     * @param string       $Value
     * @param string       $Title
     * @param array|string $JQOptions  parametry pro $.button()
     * @param array        $Properties vlastnosti HTML tagu
     */
    public function __construct($Name, $Value, $Title = null, $JQOptions = null, $Properties = null)
    {
        parent::__construct();
        $this->Name = $Name;
        $this->JQOptions = $JQOptions;
        $Properties['title']=$Title;
        $this->Button = $this->addItem(new EaseHtmlInputSubmitTag($Name, $Value, $Properties));
    }

    /**
     * Nastaveni javascriptu
     */
    public function onDocumentReady()
    {
        return '$("input[name=' . $this->Name . ']").button( {' . EaseJQueryPart::partPropertiesToString($this->JQOptions) . '} )';
    }

    /**
     * Nastaví classu tagu
     *
     * @param string $ClassName
     */
    public function setTagClass($ClassName)
    {
        return $this->Button->setTagClass($ClassName);
    }

    /**
     * Nastaví jméno tagu
     *
     * @param string $TagName
     */
    public function setTagName($TagName)
    {
        return $this->Button->setTagName($TagName);
    }

}

/**
 * A set of radio buttons transformed into a button set.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @link http://jqueryui.com/demos/button/#radio
 */
class EaseJQueryRadiobuttonGroup extends EaseHtmlRadiobuttonGroup
{

    /**
     * Doplní popisek prvku
     *
     * @param string $Label
     */
    public function addLabel($Label = null)
    {
        $ForID = $this->LastItem->getTagID();
        if (is_null($Label)) {
            $Label = $ForID;
        }
        $this->addItem('<label for="' . $ForID . '">' . $Label . '</label>');
    }

    /**
     * Doplní podporu pro jQueryUI
     */
    public function finalize()
    {
        EaseJQueryUIPart::jQueryze($this);

        $Enclosure = new EaseHtmlDivTag($this->Name . 'Group', $this->PageParts);
        unset($this->PageParts);
        $this->addItem($Enclosure);
        $this->addJavaScript('$(function () { $( "#' . $Enclosure->getTagID() . '" ).buttonset(); } );', null, true);
    }

}

/**
 * Posunovatelný blok
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EasejQueryScroller extends EaseHtmlDivTag
{

    /**
     * Objekt do nejž se vkládá rolovaný
     * @var type
     */
    public $ScrollableArea = null;

    /**
     * Rolovatelná oblast
     *
     * @param string         $Name
     * @param EasePage|mixed $Content
     * @param array          $Properties
     */
    public function __construct($Name = null, $Content = null, $Properties = null)
    {
        $Properties['id'] = $Name;
        parent::__construct($Name, $Content, $Properties);
        parent::addItem(new EaseHtmlDivTag(null, null, array('class' => 'scrollingHotSpotLeft')));
        parent::addItem(new EaseHtmlDivTag(null, null, array('class' => 'scrollingHotSpotRight')));
        $ScrollWrapper = parent::addItem(new EaseHtmlDivTag(null, null, array('class' => 'scrollWrapper')));
        $this->ScrollableArea = $ScrollWrapper->addItem(new EaseHtmlDivTag(null, null, array('class' => 'scrollableArea')));
    }

    /**
     * Vloží javascripty a csska
     */
    public function finalize()
    {
        EaseJQueryUIPart::jQueryze($this);

        EaseShared::WebPage()->includeCss('smoothDivScroll.css', true);
        EaseShared::WebPage()->includeJavaScript('jquery.smoothDivScroll-1.1.js', null, true);
        EaseShared::WebPage()->addJavaScript('
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
        return $this->ScrollableArea->addItem($PageItem,$PageItemName);
    }

}

/**
 * Vloží pole pro zadávání s měřičem jeho síly
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EasePasswordInput extends EaseHtmlInputPasswordTag
{

    /**
     * Vloží pole pro zadávání s měřičem jeho síly
     *
     * @param string $Name
     * @param string $Value
     * @param array  $Properties
     */
    public function __construct($Name, $Value = null, $Properties = null)
    {
        parent::__construct($Name, $Value, $Properties);
        $this->setTagID($Name);
    }

    /**
     * Vloží styly a scripty
     */
    public function finalize()
    {

        EaseJQueryPart::jQueryze($this);
        $this->includeJavaScript('password-strength.js', null, true);
        $this->addCSS('
.password_strength {
    padding: 0 5px;
    display: inline-block;
    }
.password_strength_1 {
    background-color: #fcb6b1;
    }
.password_strength_2 {
    background-color: #fccab1;
    }
.password_strength_3 {
    background-color: #fcfbb1;
    }
.password_strength_4 {
    background-color: #dafcb1;
    }
.password_strength_5 {
    background-color: #bcfcb1;
    }
');
        $this->addJavaScript("$('#" . $this->getTagID() . "').password_strength();", null, true);
    }

}

/**
 * Vloží pole pro zadávání hesla s kontrolou zdali souhlasí
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EasePasswordControlInput extends EaseHtmlInputPasswordTag
{

    /**
     * Vloží pole pro zadávání hesla s kontrolou zdali souhlasí
     *
     * @param string $Name
     * @param string $Value
     * @param array  $Properties
     */
    public function __construct($Name, $Value = null, $Properties = null)
    {
        parent::__construct($Name, $Value, $Properties);
        $this->setTagID($Name);
    }

    /**
     * Vloží styly a scripty
     */
    public function finalize()
    {

        EaseJQueryPart::jQueryze($this);
        $this->includeJavaScript('jquery.password-strength.js', null, true);
        $this->addCSS('
.password_control {
    padding: 0 5px;
    display: inline-block;
    }
.password_control_0 {
    background-color: #fcb6b1;
    }
.password_control_1 {
    background-color: #bcfcb1;
    }
'
        );
        $this->addJavaScript("$('#" . $this->getTagID() . "').password_control();", null, true);
    }

}

/**
 * Zobrazuje vstup pro heslo s měřičem síly opatřený patřičným popiskem
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledPasswordStrongInput extends EaseLabeledInput
{

    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name
     */
    public $ItemClass = 'EasePasswordInput';

}

/**
 * Zobrazuje vstup kontrolu hesla s indikátorem souhlasu, opatřený patřičným
 * popiskem
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledPasswordControlInput extends EaseLabeledInput
{

    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name
     */
    public $ItemClass = 'EasePasswordControlInput';

}

/**
 * Zobrazuje checkbox, opatřený patřičným popiskem
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledCheckbox extends EaseLabeledInput
{

    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name
     */
    public $ItemClass = 'EaseHtmlCheckboxTag';

}

/**
 * Zobrazuje select, opatřený patřičným popiskem
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledSelect extends EaseLabeledInput
{

    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name
     */
    public $ItemClass = 'EaseHtmlSelect';

    /**
     * Vložený select
     * @var EaseHtmlSelect
     */
    public $EnclosedElement = NULL;

    /**
     * Hromadné vložení položek
     *
     * @param array $Items položky výběru
     */
    public function addItems($Items)
    {
        return $this->EnclosedElement->addItems($Items);
    }

}

/**
 * Tlačítko s potvrzením
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @todo dodělat #IDčka ...
 */
class EaseJQConfirmedLinkButton extends EaseContainer
{

    /**
     * Link se vzhledem tlačítka a potvrzením odeslání
     *
     * @see http://jqueryui.com/demos/button/
     *
     * @param string       $Href       cíl odkazu
     * @param string       $Contents   obsah tlačítka
     * @param array|string $JQOptions  parametry pro $.button()
     * @param array        $Properties vlastnosti HTML tagu
     */
    public function __construct($Href, $Contents)
    {
        $ID = $this->randomString();
        parent::__construct(new EaseJQueryLinkButton('#', $Contents, null, array('id' => $ID . '-button')));
        $ConfirmDialog = $this->addItem(new EaseJQueryDialog($ID . '-dialog', _('potvrzení'), _('Opravdu') . ' ' . $Contents . ' ?', 'ui-icon-alert'));
        $Yes = _('Ano');
        $No = _('Ne');
        $ConfirmDialog->PartProperties = array(
            'autoOpen' => false,
            'modal' => true,
            'show' => 'slide',
            'buttons' => array(
                $Yes => 'function () { window.location.href = "' . $Href . '"; }',
                $No => 'function () { $( this ).dialog( "close" ); }'
            )
        );
        EaseShared::WebPage()->addJavascript('$( "#' . $ID
                . '-button" ).click( function () { $( "#' . $ID .
                '-dialog" ).dialog( "open" ); } );
', null, true);
    }

}

/**
 * Dialog
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 * @todo dodělat #IDčka ...
 */
class EaseJQueryDialog extends EaseJQueryUIPart
{

    /**
     * ID divu s dialogem
     * @var string
     */
    public $DialogID = NULL;

    /**
     * Titulek okna
     * @var string
     */
    public $Title = '';

    /**
     * Zpráva zobrazená v dialogu
     * @var type
     */
    public $Message = '';

    /**
     * Ikona zprávy
     * @var type
     */
    public $Icon = '';

    /**
     * Doplnující informace
     * @var type
     */
    public $Notice = NULL;

    /**
     * jQuery dialog
     *
     * @param string $DialogID id divu s dialogem
     * @param string $Title    titulek okna
     * @param mixed  $Message  obsah dialogu
     * @param string $Icon     jQueryUI ikona
     * @param string $Notice   doplnující informce
     */
    public function __construct($DialogID, $Title, $Message, $Icon = 'ui-icon-circle-check', $Notice = NULL)
    {
        $this->DialogID = $DialogID;
        $this->Title = $Title;
        $this->Message = $Message;
        $this->Icon = $Icon;
        $this->Notice = $Notice;
        $this->PartProperties = array('modal' => true, 'buttons' => array('Ok' => 'function () { $( this ).dialog( "close" ); }'));
        parent::__construct();
    }

    /**
     * Nastaveni javascriptu
     */
    public function onDocumentReady()
    {
        return '$("#' . $this->DialogID . '").dialog( {' . EaseJQueryPart::partPropertiesToString($this->PartProperties) . '} )';
    }

    /**
     * Seskládání HTML
     */
    public function finalize()
    {

        $DialogDiv = $this->addItem(new EaseHtmlDivTag(
                        $this->DialogID,
                        NULL,
                        array('title' => $this->Title))
        );

        $DialogMessage = $DialogDiv->addItem(new EaseHtmlPTag());

        $DialogMessage->addItem(
                new EaseHtmlSpanTag(NULL, NULL,
                        array('class' => 'ui-icon ' . $this->Icon,
                            'style' => 'float:left; margin:0 7px 50px 0;'
                        )
                )
        );
        $DialogMessage->addItem($this->Message);

        if (!is_null($this->Notice)) {
            $DialogDiv->addItem(new EaseHtmlPTag($this->Notice));
        }
        parent::finalize();
    }

}

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
    public $TagProperties = NULL;

    /**
     * Initial datetime
     * @var string
     */
    public $InitialValue = NULL;

    /**
     * Datetime Picker parameters
     * @var array
     */
    public $PartProperties = array(
        'dateFormat' => 'yy-mm-dd',
        'showSecond' => true,
        'timeFormat' => 'hh:mm:ss');

    /**
     * Text Input
     * @var EaseHtmlInputTextTag
     */
    public $InputTag = NULL;

    /**
     * Input for Date and time
     * @param string $PartName
     */
    public function __construct($PartName, $InitialValue = NULL, $TagProperties = NULL)
    {
        $this->TagProperties = $TagProperties;
        $this->InitialValue = $InitialValue;
        $this->SetPartName($PartName);
        parent::__construct();
        $this->EaseShared->WebPage->IncludeJavaScript('jquery-ui-timepicker-addon.js', 3, true);
        $this->EaseShared->WebPage->IncludeCss('jquery-ui-timepicker-addon.css', null, true);
        $this->InputTag = new EaseHtmlInputTextTag($this->PartName, $this->InitialValue, $this->TagProperties);
        $this->InputTag->setTagID($this->PartName);
        $this->InputTag = $this->addItem($this->InputTag);
        /*
          if ($InitialValue &&  (strtotime($InitialValue) < time( ))) {
          $this->InputTag->setTagCss(array('background-color'=>'red'));
          }
         */
    }

    /**
     * Vložení skriptu
     */
    public function finalize()
    {
        $this->EaseShared->WebPage->addJavaScript('$(function () { $( "#' . $this->PartName . '" ).datetimepicker( { ' . $this->GetPartPropertiesToString() . ' });});', 10);
    }

}

/**
 * Zobrazuje vstup pro heslo s měřičem síly opatřený patřičným popiskem
 */
class EaseLabeledDateTimeSelector extends EaseLabeledInput
{

    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name
     */
    public $ItemClass = 'LQDateTimeSelector';

}
