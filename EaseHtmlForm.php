<?php

/**
 * Třídy pro generování formulářů
 * 
 * @package    EaseFrameWork
 * @subpackage EaseHtml
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 */
require_once 'EaseHtml.php';

/**
 * Obecný input TAG
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlInputTag extends EaseHtmlTag
{

    /**
     * Nastavovat automaticky jméno tagu ?
     * 
     * @author Vítězslav Dvořák <vitex@hippy.cz>
     */
    public $SetName = true;

    /**
     * Obecný input TAG
     * 
     * @param string $Name       jméno tagu
     * @param string $Value      vracená hodnota
     * @param array  $Properties vlastnosti tagu
     */
    function __construct($Name, $Value = null, $Properties = null)
    {
        parent::__construct('input');
        $this->setTagName($Name);
        if (isset($Properties)) {
            $this->setTagProperties($Properties);
        }
        if (!is_null($Value)) {
            $this->setValue($Value);
        }
    }

    /**
     * Nastaví hodnotu vstupního políčka
     * 
     * @param string $Value vracená hodnota
     * 
     * @todo boolean $Automatic zabraňuje mazání textu z tlačítek v objektu SubmitButton
     */
    function setValue($Value)
    {
        $this->setTagProperties(array('value' => $Value));
    }

    /**
     * Vrací hodnotu vstupního políčka
     * 
     * @return string $Value
     */
    function getValue()
    {
        return $this->getTagProperty('value');
    }

}

/**
 * Zobrazí input text tag
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlInputTextTag extends EaseHtmlInputTag
{

    /**
     * Zobrazí input text tag
     * 
     * @param string $Name       jméno
     * @param string $Value      předvolená hodnota
     * @param array  $Properties dodatečné vlastnosti tagu
     */
    function __construct($Name, $Value = null, $Properties = null)
    {
        $Properties['type'] = 'text';
        if ($Value) {
            $Properties['value'] = $Value;
        }
        if ($Name) {
            $Properties['name'] = $Name;
        }
        $this->setTagProperties($Properties);
        parent::__construct($Name, $Value);
    }

}

/**
 * Zobrazí input text tag
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlInputSearchTag extends EaseHtmlInputTag
{

    /**
     * URL zdroje dat pro hinter 
     * @var string 
     */
    public $DataSourceURL = null;

    /**
     * Zobrazí tag pro vyhledávací box
     * 
     * @param string $Name       jméno
     * @param string $Value      předvolená hodnota
     * @param array  $Properties dodatečné vlastnosti tagu
     */
    function __construct($Name, $Value = null, $Properties = null)
    {
        $Properties['type'] = 'search';
        if ($Value) {
            $Properties['value'] = $Value;
        }
        if ($Name) {
            $Properties['name'] = $Name;
        }
        if (!isset($Properties['id'])) {
            $this->setTagID($Name . EaseBrick::randomString());
        }
        $this->setTagProperties($Properties);
        parent::__construct($Name, $Value);
    }

    /**
     * Nastaví zdroj dat našeptávače
     * 
     * @param string $DataSourceURL url zdroje dat našeptávače ve formátu JSON
     */
    function setDataSource($DataSourceURL)
    {
        $this->DataSourceURL = $DataSourceURL;
    }

    /**
     * Vloží do stránky scripty pro hinter
     */
    function finalize()
    {
        if (!is_null($this->DataSourceURL)) {
            EaseJQueryUIPart::jQueryze($this);

            $this->addCSS('.ui-autocomplete-loading { background: white url(\'Ease/css/images/ui-anim_basic_16x16.gif\') right center no-repeat; }');

            $this->addJavaScript('
    $( "#' . $this->getTagID() . '" ).bind( "keydown", function( event ) {
            if ( event.keyCode === $.ui.keyCode.TAB &&
                            $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
            }
    })
    .autocomplete({
            source: function( request, response ) {
                    $.getJSON( "' . $this->DataSourceURL . '", { term: request.term }, response );
            },
            focus: function() {
                    // prevent value inserted on focus
                    return false;
            },
            open: function() {
                    $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
            },
            close: function() {
                    $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
            }
    });



            ', null, true
            );
        }
    }

}

/**
 * Skrytý input
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlInputHiddenTag extends EaseHtmlInputTag
{

    /**
     * Skrytý input
     * 
     * @param string $Name       jméno tagu
     * @param string $Value      vracená hodnota
     * @param array  $Properties vlastnosti tagu 
     */
    function __construct($Name, $Value = null, $Properties = null)
    {
        parent::__construct($Name, $Value);
        $Properties['type'] = 'hidden';
        $this->setTagProperties($Properties);
    }

}

/**
 * Radio button
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlInputRadioTag extends EaseHtmlInputTag
{

    /**
     * Vracená hodnota
     * @var string 
     */
    public $Value = null;

    /**
     * Radio button
     * 
     * @param string $Name          jméno tagu
     * @param string $Value         vracená hodnota
     * @param array  $TagProperties vlastnosti tagu
     */
    function __construct($Name, $Value = null, $TagProperties = null)
    {
        parent::__construct($Name, $Value);
        if ($TagProperties) {
            $this->setTagProperties($TagProperties);
        }
        $this->setTagProperties(array('type' => 'radio'));
        $this->Value = $Value;
    }

    /**
     * Poprvé nastaví hodnotu checkboxu. Druhé volání nastavuje příznak checked, 
     * pokud je hodnota stejná jako již nabitá
     * 
     * @param string $Value vracená hodnota
     * 
     * @todo boolean $Automatic zabraňuje mazání textu z tlačítek v objektu SubmitButton
     */
    function setValue($Value)
    {
        $CurrentValue = $this->getTagProperty('value');
        if ($CurrentValue) {
            if ($CurrentValue == $Value) {
                $this->setTagProperties(array('checked'));
            }
        } else {
            $this->setTagProperties(array('value' => $Value));
        }
    }

    /* TODO:
      function  Finalize() {
      if (isset($this->TagProperties['value']) && $this->TagProperties['value'] && ($this->TagProperties['value'] == $this->Value)) {
      $this->setTagProperties(array('checked'));
      }

      }
     *
     */
}

/**
 * Skupina vstupních prvků
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseInputContainer extends EaseContainer
{

    /**
     * Name of Radios
     * @var string 
     */
    public $Name = 'container';

    /**
     * Stored values
     * @var array 
     */
    public $Items = array();

    /**
     * Default value
     * @var mixed 
     */
    public $Checked = null;

    /**
     * ClassName
     * @var EaseHtmlInputTag or childs 
     */
    public $ItemClass = 'EaseHtmlInputTextTag';

    /**
     * Skupina inputů 
     * 
     * @param string $Name          výchozí jméno tagů
     * @param array  $Items         pole položek
     * @param string $TagProperties parametry tagů
     */
    function __construct($Name, $Items = null, $TagProperties = null)
    {
        parent::__construct();
        $this->Name = $Name;
        $this->Items = $Items;
    }

    /**
     * Nastaví hodnotu vstupního políčka
     * 
     * @param string $Value hodnota
     */
    function setValue($Value)
    {
        $this->Checked = $Value;
    }

    /**
     * Vrací hodnotu vstupního políčka
     * 
     * @param bool $Value hodnota je ignorována
     * 
     * @return string $Value binární hodnota - stav
     */
    function getValue($Value)
    {
        return $this->Checked;
    }

    /**
     * Return assigned form input Tag name
     * 
     * @return string
     */
    function getTagName()
    {
        return $this->Name;
    }

    /**
     * Vloží podprvky
     */
    function finalize()
    {
        $ItemID = 1;
        foreach ($this->Items as $Value => $Caption) {
            if ($this->Checked == $Value) {
                $this->addItem(new $this->ItemClass($this->Name, $Value, array('checked')));
            } else {
                $this->addItem(new $this->ItemClass($this->Name, $Value));
            }
            $this->LastItem->setTagID($this->Name . $ItemID++);
            $this->addLabel($Caption);
        }
    }

    /**
     * Doplní popisek prvku
     * 
     * @param string $Label text popisku
     */
    function addLabel($Label = null)
    {
        $ForID = $this->LastItem->getTagID();
        if (is_null($Label)) {
            $Label = $ForID;
        }
        $this->addItem('<label for="' . $ForID . '">' . $Label . '</label>');
    }

}

/**
 * Group of RadioButtons
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlRadiobuttonGroup extends EaseInputContainer
{

    /**
     * 
     * @var string 
     */
    public $ItemClass = 'EaseHtmlInputRadioTag';

}

/**
 * Group of CheckBoxes
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlCheckboxGroup extends EaseInputContainer
{

    public $ItemClass = 'EaseHtmlCheckboxTag';

    /**
     * Pocet vlozenych polozek
     * @var int 
     */
    private $_subitemCount = 0;

    /**
     * Pole hodnot k nastavení
     * @var array  
     */
    public $Values = array();

    /**
     * Skupina checkboxů
     * 
     * @param string $Name
     * @param array  $Items
     * @param array  $ItemValues
     * @param array  $TagProperties 
     */
    function __construct($Name, $Items = null, $ItemValues = null, $TagProperties = null)
    {
        parent::__construct($Name, $Items, $TagProperties);
        if (!is_null($ItemValues)) {
            $Values = array();
            foreach ($ItemValues as $ItemName => $Item) {
                $Values[$Name . '_' . $ItemName] = $Item;
            }
            $this->setValues($Values);
        }
    }

    /**
     * Přejmenuje vložené checkboxy pro použití ve formuláři
     * 
     * @param EaseHtmlCheckboxTag $PageItem vkládaný objekt CheckBoxu
     * @param string              $PageItemName Pod tímto jménem je objekt vkládán do stromu
     * 
     * @return EaseHtmlCheckboxTag 
     */
    function &addItem($PageItem,$PageItemName = null)
    {
        /**
         * Allready Added Item 
         * @var EaseHtmlCheckboxTag
         */
        $ItemInpage = parent::addItem($PageItem);
        if (is_object($ItemInpage)) {
            if (isset($this->Items)) {
                $Keys = array_keys($this->Items);
                $ItemInpage->setTagProperties(array('name' => $ItemInpage->getTagProperty('name') . '#' . $Keys[$this->_subitemCount]));
                if (isset($this->Values[$Keys[$this->_subitemCount]])) {
                    $ItemInpage->setValue((bool) $this->Values[$Keys[$this->_subitemCount]]);
                }
                next($this->Items);
                $this->_subitemCount++;
            }
        }
        return $ItemInpage;
    }

    /**
     * Vložení jména skupiny
     */
    function finalize()
    {
        parent::finalize();
        parent::addItem(new EaseHtmlInputHiddenTag('CheckBoxGroups[' . $this->Name . ']', $this->getTagName()));
    }

    /**
     * Poprvé nastaví hodnotu checkboxu. Druhé volání nastavuje příznak checked, 
     * pokud je hodnota stejná jako již nabitá
     * 
     * @param string $Value vracená hodnota
     * 
     * @todo boolean $Automatic zabraňuje mazání textu z tlačítek v objektu SubmitButton
     */
    function setValue($Value)
    {
        $CurrentValue = $this->GetTagProperty('value');
        if ($CurrentValue) {
            if ($CurrentValue == $Value) {
                $this->setTagProperties(array('checked'));
            }
        } else {
            $this->setTagProperties(array('value' => $Value));
        }
    }

    /**
     * Nastaví hodnoty položek
     * 
     * @param array $Values pole hodnot
     */
    function setValues($Values)
    {
        $TagName = $this->getTagName();
        foreach (array_keys($this->Items) as $ItemKey) {
            if (isset($Values[$TagName . '_' . $ItemKey])) {
                $this->Values[$ItemKey] = $Values[$TagName . '_' . $ItemKey];
            }
        }
    }

}

/**
 * Vstupní prvek pro odeslání souboru
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlInputFileTag extends EaseHtmlInputTag
{

    /**
     * Vstupní box pro volbu souboru
     * 
     * @param string $Name  jméno tagu
     * @param string $Value předvolená hodnota
     */
    function __construct($Name, $Value = null)
    {
        parent::__construct($Name, $Value);
        $this->setTagProperties(array('type' => 'file'));
    }

}

/**
 * Vstup pro zadání hesla
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlInputPasswordTag extends EaseHtmlInputTextTag
{

    /**
     * Input pro heslo
     * 
     * @param string $Name  jméno tagu
     * @param string $Value předvolené heslo
     */
    function __construct($Name, $Value = null)
    {
        parent::__construct($Name, $Value);
        $this->setTagProperties(array('type' => 'password'));
    }

}

/**
 * Zobrazí tag pro chcekbox
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlCheckboxTag extends EaseHtmlInputTag
{

    /**
     * Zobrazuje HTML Checkbox
     * 
     * @param string $Name       jméno tagu
     * @param bool   $Checked    stav checkboxu
     * @param string $Value      vracená hodnota checkboxu
     * @param array  $Properties parametry tagu
     */
    function __construct($Name, $Checked = false, $Value = null, $Properties = null)
    {
        if ($Properties) {
            $Properties['type'] = 'checkbox';
        } else {
            $Properties = array('type' => 'checkbox');
        }
        if ($Checked) {
            $Properties['checked'] = 'true';
        }
        if ($Value) {
            $Properties['value'] = $Value;
        }
        if ($Name != '') {
            $Properties['name'] = $Name;
        }
        $this->setTagProperties($Properties);
        parent::__construct($Name);
    }

    /**
     * Nastaví zaškrtnutí 
     * 
     * @param boolean $Value nastavuje parametr "checked" tagu
     */
    function setValue($Value = true)
    {
        if ($Value) {
            $this->setTagProperties(array('checked' => 'true'));
        } else {
            unset($this->TagProperties['checked']);
        }
    }

}

/**
 * Odesílací tlačítko
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseSubmitButton extends EaseHtmlInputTag
{

    /**
     * Popisek odesílacího tlačítka
     * @var string
     */
    public $Label = null;

    /**
     * Odesílací tlačítko
     * <input type="submit" name="$label" value="$Value" title="$Hint">
     * 
     * @param string $Label    nápis na tlačítku
     * @param string $Value    odesílaná hodnota
     * @param string $Hint     tip při najetí myší
     * @param string $ClassCss css třída pro tag tlačítka
     */
    function __construct($Label, $Value = null, $Hint = null, $ClassCss = null)
    {
        $Properties = array('type' => 'submit');
        if (!$Value) {
            $Value = trim(str_replace(array(' ', '?'), '', @iconv("utf-8", "us-ascii//TRANSLIT", strtolower($Label))));
        } else {
            $Properties['value'] = $Value;
        }
        if ($Hint) {
            $Properties['title'] = $Hint;
        }
        if ($ClassCss) {
            $Properties['class'] = $ClassCss;
        }
        $this->setTagProperties($Properties);
        parent::__construct($Value, $Label);
        $this->Label = $Label;
    }

    /**
     * Nastaví hodnotu
     * 
     * @param string  $Value     vracená hodnota tagu
     * @param boolean $Automatic Hack pro zachování labelů při plnění formuláře
     */
    function setValue($Value, $Automatic = false)
    {
        if (!$Automatic) { //FillUp nenastavuje Labely tlačítek
            parent::SetValue($Value);
        }
    }

}

/**
 * Odeslání formuláře obrázkem
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseImageSubbmit extends EaseHtmlInputTag
{

    /**
     * Zobrazí <input type="image">
     * 
     * @param string $Image url obrázku
     * @param string $Label popisek obrázku
     * @param string $Value vracená hodnota
     * @param string $Hint  text tipu
     */
    function __construct($Image, $Label, $Value = null, $Hint = null)
    {
        $Properties = array('type' => 'image');
        if (!$Value) {
            $Value = trim(str_replace(array(' ', '?'), '', @iconv("utf-8", "us-ascii//TRANSLIT", strtolower($Label))));
        } else {
            $Properties['value'] = $Value;
        }
        if ($Hint) {
            $Properties['title'] = $Hint;
        }
        $Properties['src'] = $Image;
        $this->setTagProperties($Properties);
        parent::__construct($Value, $Label);
    }

}

/**
 * Odesílací tlačítko formuláře
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlInputSubmitTag extends EaseHtmlInputTag
{

    /**
     * Odesílací tlačítko formuláře
     * 
     * @param string $Name  jméno tagu
     * @param string $Value vracená hodnota
     * @param array $Properties Pole vlastností tagu
     */
    function __construct($Name, $Value = null, $Properties = null)
    {
        if (!$Value) {
            $Value = $Name;
        }
        if (is_null($Properties)){
            $Properties = array();
        }
        $Properties['type'] = 'submit';
        $Properties['name'] = $Name;
        $Properties['value'] = $Value;
        parent::__construct($Name, $Value, $Properties);
    }

    /**
     * Maketa kuli popisku
     * 
     * @param bool $Value je ignorováno
     */
    function setValue($Value = true)
    {
        
    }

}

/**
 * Textové pole
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlTextareaTag extends EaseHtmlPairTag
{

    /**
     * Odkaz na obsah
     */
    public $Content = null;

    /**
     * 
     */
    public $SetName = true;

    /**
     * Textarea
     * 
     * @param string $Name       jméno tagu
     * @param string $Content    obsah textarey
     * @param array  $Properties vlastnosti tagu 
     */
    function __construct($Name, $Content = '', $Properties = null)
    {
        $this->setTagName($Name);
        parent::__construct('textarea', $Properties);
        if ($Content) {
            $this->addItem($Content);
        }
    }

    /**
     * Nastaví obsah
     * 
     * @param string $Value hodnota
     */
    function setValue($Value)
    {
        $this->PageParts = array();
        $this->addItem($Value);
    }

}

/**
 * Položka seznamu
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlOptionTag extends EaseHtmlPairTag
{

    /**
     * Hodnota
     * @var string 
     */
    public $Value = null;

    /**
     * Tag položky rozbalovací nabídky
     * 
     * @param string|mixed $Content text volby
     * @param string|int   $Value   vracená hodnota
     */
    function __construct($Content, $Value = null)
    {
        parent::__construct('option', array('value' => $Value), $Content);
        $this->setObjectName($this->getObjectName() . '@' . $Value);
        $this->Value = &$this->TagProperties['value'];
    }

    /**
     * Nastaví předvolenou položku
     */
    function setDefault()
    {
        return $this->setTagProperties(array('selected'));
    }

    /**
     * Nastaví hodnotu
     * 
     * @param int|string $Value vracená hodnota
     */
    function setValue($Value)
    {
        $this->Value = $Value;
    }

}

/**
 * Html Select
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlSelect extends EaseHtmlPairTag
{

    /**
     * Předvolené položka #
     * @var int 
     */
    public $DefaultValue = null;

    /**
     * Automaticky nastavovat název elemnetu
     * @var boolean 
     */
    public $SetName = true;

    /**
     * @var pole hodnot k nabídnutí selectu
     */
    public $Items = array();

    /**
     * Mají se vloženým položkám nastavovat ID ?
     * @var boolean
     */
    private $_itemsIDs = false;

    /**
     * Cache zobrazovaných hodnot
     * @var array
     */
    private $_cache = array();

    /**
     * Html select box
     * 
     * @param string $Name         jmeno
     * @param array  $Items        polozky
     * @param mixed  $DefaultValue id predvolene polozky
     * @param array  $ItemsIDs     id položek
     * @param array  $Properties   tag properties
     */
    function __construct($Name, $Items = null, $DefaultValue = null, $ItemsIDs = false, $Properties = null)
    {
        parent::__construct('select', $Properties);
        $this->DefaultValue = $DefaultValue;
        $this->_itemsIDs = $ItemsIDs;
        $this->setTagName($Name);
        if (is_array($Items)) {
            $this->addItems($Items);
        }
    }
    
    /**
     * Hromadné vložení položek
     * 
     * @param array $Items položky výběru
     */
    function addItems($Items)
    {
        foreach ($Items as $ItemName => $ItemValue) {
            $NewItem = $this->addItem(new EaseHtmlOptionTag($ItemValue, $ItemName));
            if ($this->_itemsIDs) {
                $NewItem->setTagID($this->getTagName() . $ItemName);
            }
            if ($this->DefaultValue == $ItemValue) {
                $this->LastItem->setDefault();
            }
        }
    }

    /**
     * Vloží hodnotu
     * 
     * @param string $Value   hodnota
     * @param string $ValueID id hodnoty
     */
    function addValue($Value, $ValueID = 0)
    {
        $this->addItems(array($ValueID => $Value));
    }

    /**
     * Maketa načtení položek
     * 
     * @return string
     */
    function loadItems()
    {
        return array();
    }

    /**
     * Nastavení hodnoty
     * 
     * @param string $Value nastavovaná hodnota
     */
    function setValue($Value)
    {
        if (trim(strlen($Value))) {
            foreach ($this->PageParts as $Option) {
                if ($Option->Value == $Value) {
                    $Option->setDefault();
                } else {
                    unset($Option->TagProperties['selected']);
                }
            }
        } else {
            if (isset($this->PageParts) && count($this->PageParts)) {
                $FirstItem = &reset($this->PageParts);
                $FirstItem->setDefault();
            }
        }
    }

    /**
     * Vložit načtené položky
     */
    function finalize()
    {
        if (!count($this->PageParts)) { //Uninitialised Select - so we load items
            $this->addItems($this->loadItems());
        }
    }

    /**
     * Odstarní položku z nabídky
     * 
     * @param string $ItemID klíč hodnoty k odstranění ze seznamu
     */
    public function delItem($ItemID)
    {
        unset($this->PageParts['EaseHtmlOptionTag@' . $ItemID]);
    }

}

/**
 * Html formulář se schopností rekurzivne naplnit hodnotami vložené prvky 
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlForm extends EaseHtmlPairTag
{

    /**
     * Cíl formu
     * @var string URL cíle formuláře 
     */
    public $FormTarget = null;

    /**
     * Metoda odesílání
     * @var string GET|POST 
     */
    public $FormMethod = null;

    /**
     * Nastavovat formuláři jméno ?
     * @var type 
     */
    public $SetName = false;

    /**
     * Zobrazí html formulář
     * 
     * @param string $FormName      jméno formuláře
     * @param string $FormAction    cíl formulář např login.php
     * @param string $FormMethod    metoda odesílání POST|GET
     * @param mixed  $FormContents  prvky uvnitř formuláře
     * @param array  $TagProperties vlastnosti tagu například: 
     *                                      array('enctype' => 'multipart/form-data')
     */
    function __construct($FormName, $FormAction = null, $FormMethod = 'post', $FormContents = null, $TagProperties = null)
    {
        parent::__construct('form', array('method' => $FormMethod, 'name' => $FormName));
        if ($FormAction) {
            $this->setFormTarget($FormAction);
        } else {
            $this->setFormTarget($_SERVER['REQUEST_URI']);
        }
        if (isset($FormContents)) {
            $this->addItem($FormContents);
        }
        if (!is_null($TagProperties)) {
            $this->setTagProperties($TagProperties);
        }
    }

    /**
     * Nastaví cíl odeslání
     * 
     * @param string $FormTarget cíl odeslání formuláře
     */
    function setFormTarget($FormTarget)
    {
        $this->FormTarget = $FormTarget;
        $this->setTagProperties(array('action' => $FormTarget));
    }

    /**
     * Změní jeden nebo více parametrů v ACTION url formuláře
     * 
     * @param array $ParametersToChange pole parametrů
     * @param bool  $Replace            přepisovat již existující
     */
    function changeActionParameter($ParametersToChange, $Replace = true)
    {
        if (is_array($ParametersToChange) && count($ParametersToChange)) {
            foreach ($ParametersToChange as $ParamName => $ParamValue) {
                if ($ParamValue == true) {
                    unset($ParametersToChange[$ParamName]);
                }
            }
            $TargetParts = explode('&', str_replace('&&', '&', str_replace('?', '&', $this->FormTarget)));
            if (is_array($TargetParts) && count($TargetParts)) {
                $FormTargetComputed = '';
                $TargetPartsValues = array();
                foreach ($TargetParts as $TargetPart) {
                    if (!strstr($TargetPart, '=')) {
                        $FormTargetComputed .= $TargetPart;
                        continue;
                    }
                    list($TargetPartName, $TargetPartValue) = explode('=', $TargetPart);
                    if ($TargetPartValue == true) {
                        continue;
                    }
                    $TargetPartsValues[$TargetPartName] = $TargetPartValue;
                }
            }
            if ($Replace) {
                $NewTargPartVals = array_merge($TargetPartsValues, $ParametersToChange);
            } else {
                $NewTargPartVals = array_merge($ParametersToChange, $TargetPartsValues);
            }
            $GlueSign = '?';
            foreach ($NewTargPartVals as $NewTargetPartsValName => $NewTargetPartsValue) {
                $FormTargetComputed .= $GlueSign . urlencode($NewTargetPartsValName) . '=' . urlencode($NewTargetPartsValue);
                $GlueSign = '&';
            }
            $this->setFormTarget($FormTargetComputed);
        }
    }

    /**
     * Pokusí se najít ve vložených objektech tag zadaného jména
     * 
     * @param string        $SearchFor jméno hledaného elementu
     * @param EaseContainer $Where     objekt v němž je hledáno
     * 
     * @return EaseContainer|class 
     */
    function & objectContentSearch($SearchFor, $Where = null)
    {
        if (is_null($Where)) {
            $Where = & $this;
        }
        $ItemFound = null;
        if (isset($Where->PageParts) && is_array($Where->PageParts) && count($Where->PageParts)) {
            foreach ($Where->PageParts as $PagePart) {
                if (is_object($PagePart)) {
                    if (method_exists($PagePart, 'GetTagName')) {
                        if ($PagePart->getTagName() == $SearchFor) {
                            return $PagePart;
                        }
                    } else {
                        $ItemFound = $this->objectContentSearch($SearchFor, $PagePart);
                        if ($ItemFound) {
                            return $ItemFound;
                        }
                    }
                }
            }
        }
        return $ItemFound;
    }

    /**
     * Doplnění perzistentních hodnot
     */
    public function finalize()
    {
        $this->setupWebPage();
        if (isset($this->WebPage->RequestValuesToKeep) && is_array($this->WebPage->RequestValuesToKeep) && count($this->WebPage->RequestValuesToKeep)) {
            foreach ($this->WebPage->RequestValuesToKeep as $Name => $Value) {
                if (!$this->objectContentSearch($Name)) {
                    if (is_string($Value)) {
                        $this->addItem(new EaseHtmlInputHiddenTag($Name, $Value));
                    }
                }
            }
        }
    }

}

/**
 * Tag Label pro LabeledInput
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlLabelTag extends EaseHtmlPairTag
{

    /**
     * Odkaz na obsah
     * @var mixed 
     */
    public $Contents = NULL;

    /**
     * zobrazí tag pro návěští
     * 
     * @param string $For        vztažný element
     * @param mixed  $Contents   obsah opatřovaný popiskem
     * @param array  $Properties vlastnosti tagu
     */
    function __construct($For, $Contents = null, $Properties = null)
    {
        $this->setTagProperties(array('for' => $For));
        parent::__construct('label', $Properties);
        $this->Contents = $this->addItem($Contents);
    }

    /**
     * Nastaví jméno objektu
     * 
     * @param string $ObjectName nastavované jméno
     * 
     * @return string New object name 
     */
    function setObjectName($ObjectName = null)
    {
        if ($ObjectName) {
            return parent::setObjectName($ObjectName);
        }
        return parent::setObjectName(get_class($this) . '@' . $this->getTagProperty('for'));
    }

}

/**
 * Zobrazuje obecný input opatřený patřičným popiskem
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledInput extends EasePage
{

    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name 
     */
    public $ItemClass = 'EaseHtmlInputTag';

    /**
     * Objekt olabelovaný
     * @var EaseHtmlInputTag 
     */
    public $EnclosedElement = null;

    /**
     *
     * @var type 
     */
    public $LabelElement = null;

    /**
     * obecný input opatřený patřičným popiskem
     * 
     * @param string $Name       jméno 
     * @param string $Value      hondnota
     * @param string $Label      popisek 
     * @param array  $Properties vlastnosti tagu
     */
    function __construct($Name, $Value = null, $Label = null, $Properties = null)
    {
        parent::__construct();
        if (!isset($Properties['id'])) {
            $Properties['id'] = $Name . 'TextInput';
        }
        if ($Label) {
            $this->addCSS(
                    '.InputCaption {
cursor: pointer; 
display: block;
font-size: x-small;
margin-top: 5px;}'
            );
            $this->LabelElement = new EaseHtmlLabelTag($Properties['id'], $Label, array('class' => 'InputCaption'));
        }

        switch ($this->ItemClass) {
            case 'EaseHtmlCheckboxTag':
                $this->EnclosedElement = new $this->ItemClass($Name, $Value, $Value, $Properties);
                break;
            case 'EaseHtmlSelect':
                //function __construct($Name, $Items = null, $DefaultValue = null, $ItemsIDs = false, $Properties = null)
                $this->EnclosedElement = new $this->ItemClass($Name, null ,$Value, false, $Properties);
                break;
            default:
                $this->EnclosedElement = new $this->ItemClass($Name, $Value, $Properties);
                break;
        }
    }

    /**
     * Seskládání
     */
    function finalize()
    {
        $this->addItem($this->LabelElement);
        $this->addItem($this->EnclosedElement);
    }

    /**
     * Vrací parametry obsaženého tagu
     * 
     * @return string 
     */
    function getTagProperties()
    {
        if (method_exists($this->EnclosedElement, 'getTagProperties')) {
            return $this->EnclosedElement->getTagProperties();
        }
    }

    /**
     * Vrací hodnotu parametru vloženého tagu
     * 
     * @param string $PropertyName název parametru
     * 
     * @return string|int|null hodnota parametru 
     */
    public function getTagProperty($PropertyName)
    {
        if (method_exists($this->EnclosedElement, 'getTagProperties')) {
            return $this->EnclosedElement->getTagProperty($PropertyName);
        }
    }

    /**
     * Vrací jméno vloženého tagu
     * 
     * @return string|null 
     */
    public function getTagName()
    {
        if (method_exists($this->EnclosedElement, 'getTagName')) {
            return $this->EnclosedElement->getTagName();
        }
    }

    /**
     * Nastaví hodnotu vloženého tagu
     * 
     * @param int|string $value
     * @return null 
     */
    function setValue($value)
    {
        if (method_exists($this->EnclosedElement, 'setValue')) {
            return $this->EnclosedElement->setValue($value);
        }
        return null;
    }

    /**
     * Nastaví pole hodnot vloženému tagu např. poli checkboxů
     * 
     * @param int|string $values
     * @return null 
     */
    function setValues($values)
    {
        if (method_exists($this->EnclosedElement, 'setValues')) {
            return $this->EnclosedElement->setValues($values);
        }
        return null;
    }

}

/**
 * Zobrazuje textový input opatřený patřičným popiskem
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledTextInput extends EaseLabeledInput
{

    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name 
     */
    public $ItemClass = 'EaseHtmlInputTextTag';

}

/**
 * Zobrazuje vyhledávací box opatřený patřičným popiskem
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledSearchInput extends EaseLabeledInput
{

    /**
     * Který input opatřit labelem ?
     * 
     * @var string EaseInputClass name 
     */
    public $ItemClass = 'EaseHtmlInputSearchTag';

    /**
     * 
     */
    function setDataSource($DataSource)
    {
        $this->EnclosedElement->setDataSource($DataSource);
    }

}
/**
 * Zobrazuje souborový input opatřený patřičným popiskem
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledFileInput extends EaseLabeledInput
{

    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name 
     */
    public $ItemClass = 'EaseHtmlInputFileTag';

}
/**
 * Zobrazuje textový input opatřený patřičným popiskem
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledTextarea extends EaseLabeledInput
{

    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name 
     */
    public $ItemClass = 'EaseHtmlTextareaTag';

}

/**
 * Zobrazuje vstup pro heslo opatřený patřičným popiskem
 * 
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledPasswordInput extends EaseLabeledInput
{

    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name 
     */
    public $ItemClass = 'EaseHtmlInputPasswordTag';

}

?>
