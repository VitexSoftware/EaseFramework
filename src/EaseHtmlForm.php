<?php

/**
 * Třídy pro generování formulářů
 *
 * @package    EaseFrameWork
 * @subpackage EaseHtml
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 */
require_once 'EaseHtmlPairTag.php';

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
    public $setName = true;

    /**
     * Obecný input TAG
     *
     * @param string             $name       jméno tagu
     * @param string|EaseObject  $value      vracená hodnota
     * @param array              $properties vlastnosti tagu
     */
    public function __construct($name, $value = null, $properties = null)
    {
        parent::__construct('input');
        $this->setTagName($name);
        if (isset($properties)) {
            $this->setTagProperties($properties);
        }
        if (!is_null($value)) { //Pokud je hodnota EaseObjekt, vytáhne si hodnotu políčka z něj
            if (is_object($value) && method_exists($value, 'getDataValue')) {
                $value = $content->getDataValue($name);
            }
            $this->setValue($value);
        }
    }

    /**
     * Nastaví hodnotu vstupního políčka
     *
     * @param string $value vracená hodnota
     *
     * @todo boolean $Automatic zabraňuje mazání textu z tlačítek v objektu SubmitButton
     */
    public function setValue($value)
    {
        $this->setTagProperties(array('value' => $value));
    }

    /**
     * Vrací hodnotu vstupního políčka
     *
     * @return string $value
     */
    public function getValue()
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
     * @param string $name       jméno
     * @param string $value      předvolená hodnota
     * @param array  $properties dodatečné vlastnosti tagu
     */
    public function __construct($name, $value = null, $properties = null)
    {
        if (!isset($properties['type'])) {
            $properties['type'] = 'text';
        }
        if ($value) {
            $properties['value'] = $value;
        }
        if ($name) {
            $properties['name'] = $name;
        }
        $this->setTagProperties($properties);
        parent::__construct($name, $value);
    }

}

/**
 * Vstupní pole čísla
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlInputNumberTag extends EaseHtmlInputTag
{

    /**
     * Vstupní pole čísla
     *
     * @param string $name       jméno
     * @param string $value      předvolená hodnota
     * @param array  $properties dodatečné vlastnosti tagu
     */
    public function __construct($name, $value = null, $properties = null)
    {
        $properties['type'] = 'number';
        if ($value) {
            $properties['value'] = $value;
        }
        if ($name) {
            $properties['name'] = $name;
        }
        $this->setTagProperties($properties);
        parent::__construct($name, $value);
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
    public $dataSourceURL = null;

    /**
     * Zobrazí tag pro vyhledávací box
     *
     * @param string $name       jméno
     * @param string $value      předvolená hodnota
     * @param array  $properties dodatečné vlastnosti tagu
     */
    public function __construct($name, $value = null, $properties = null)
    {
        $properties['type'] = 'search';
        if ($value) {
            $properties['value'] = $value;
        }
        if ($name) {
            $properties['name'] = $name;
        }
        if (!isset($properties['id'])) {
            $this->setTagID($name . EaseBrick::randomString());
        }
        $this->setTagProperties($properties);
        parent::__construct($name, $value);
    }

    /**
     * Nastaví zdroj dat našeptávače
     *
     * @param string $DataSourceURL url zdroje dat našeptávače ve formátu JSON
     */
    public function setDataSource($DataSourceURL)
    {
        $this->dataSourceURL = $DataSourceURL;
    }

    /**
     * Vloží do stránky scripty pro hinter
     */
    public function finalize()
    {
        if (!is_null($this->dataSourceURL)) {
            EaseJQueryUIPart::jQueryze($this);

            $this->addCSS('.ui-autocomplete-loading { background: white url(\'Ease/css/images/ui-anim_basic_16x16.gif\') right center no-repeat; }');

            $this->addJavaScript('
    $( "#' . $this->getTagID() . '" ).bind( "keydown", function (event) {
            if ( event.keyCode === $.ui.keyCode.TAB &&
                            $( this ).data( "autocomplete" ).menu.active ) {
                    event.preventDefault();
            }
    })
    .autocomplete({
            source: function (request, response) {
                    $.getJSON( "' . $this->dataSourceURL . '", { term: request.term }, response );
            },
            focus: function () {
                    // prevent value inserted on focus
                    return false;
            },
            open: function () {
                    $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
            },
            close: function () {
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
     * @param string $name       jméno tagu
     * @param string $value      vracená hodnota
     * @param array  $properties vlastnosti tagu
     */
    public function __construct($name, $value = null, $properties = null)
    {
        parent::__construct($name, $value);
        $properties['type'] = 'hidden';
        $this->setTagProperties($properties);
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
    public $value = null;

    /**
     * Radio button
     *
     * @param string $name          jméno tagu
     * @param string $value         vracená hodnota
     * @param array  $tagProperties vlastnosti tagu
     */
    public function __construct($name, $value = null, $tagProperties = null)
    {
        parent::__construct($name, $value);
        if ($tagProperties) {
            $this->setTagProperties($tagProperties);
        }
        $this->setTagProperties(array('type' => 'radio'));
        $this->Value = $value;
    }

    /**
     * Poprvé nastaví hodnotu checkboxu. Druhé volání nastavuje příznak checked,
     * pokud je hodnota stejná jako již nabitá
     *
     * @param string $value vracená hodnota
     *
     * @todo boolean $Automatic zabraňuje mazání textu z tlačítek v objektu SubmitButton
     */
    public function setValue($value)
    {
        $CurrentValue = $this->getTagProperty('value');
        if ($CurrentValue) {
            if ($CurrentValue == $value) {
                $this->setTagProperties(array('checked'));
            }
        } else {
            $this->setTagProperties(array('value' => $value));
        }
    }

    /* TODO:
      function Finalize()
      {
      if (isset($this->tagProperties['value']) && $this->tagProperties['value'] && ($this->tagProperties['value'] == $this->Value)) {
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
    public $name = 'container';

    /**
     * Stored values
     * @var array
     */
    public $items = array();

    /**
     * Default value
     * @var mixed
     */
    public $checked = null;

    /**
     * ClassName
     * @var EaseHtmlInputTag or childs
     */
    public $itemClass = 'EaseHtmlInputTextTag';

    /**
     * Skupina inputů
     *
     * @param string $name          výchozí jméno tagů
     * @param array  $items         pole položek
     * @param string $tagProperties parametry tagů
     */
    public function __construct($name, $items = null, $tagProperties = null)
    {
        parent::__construct();
        $this->name = $name;
        $this->items = $items;
    }

    /**
     * Nastaví hodnotu vstupního políčka
     *
     * @param string $value hodnota
     */
    public function setValue($value)
    {
        $this->checked = $value;
    }

    /**
     * Vrací hodnotu vstupního políčka
     *
     * @param bool $value hodnota je ignorována
     *
     * @return string $value binární hodnota - stav
     */
    public function getValue($value)
    {
        return $this->checked;
    }

    /**
     * Return assigned form input Tag name
     *
     * @return string
     */
    public function getTagName()
    {
        return $this->name;
    }

    /**
     * Vloží podprvky
     */
    public function finalize()
    {
        $itemID = 1;
        foreach ($this->items as $value => $caption) {
            if ($this->checked == $value) {
                $this->addItem(new $this->itemClass($this->name, $value, array('checked')));
            } else {
                $this->addItem(new $this->itemClass($this->name, $value));
            }
            $this->lastItem->setTagID($this->name . $itemID++);
            $this->addLabel($caption);
        }
        $this->finalized = true;
    }

    /**
     * Doplní popisek prvku
     *
     * @param string $label text popisku
     */
    public function addLabel($label = null)
    {
        $forID = $this->lastItem->getTagID();
        if (is_null($label)) {
            $label = $forID;
        }
        $this->addItem('<label for="' . $forID . '">' . $label . '</label>');
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
    public $itemClass = 'EaseHtmlInputRadioTag';

}

/**
 * Group of CheckBoxes
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseHtmlCheckboxGroup extends EaseInputContainer
{

    public $itemClass = 'EaseHtmlCheckboxTag';

    /**
     * Pocet vlozenych polozek
     * @var int
     */
    private $_subitemCount = 0;

    /**
     * Pole hodnot k nastavení
     * @var array
     */
    public $values = array();

    /**
     * Skupina checkboxů
     *
     * @param string $name
     * @param array  $items
     * @param array  $itemValues
     * @param array  $tagProperties
     */
    public function __construct($name, $items = null, $itemValues = null, $tagProperties = null)
    {
        parent::__construct($name, $items, $tagProperties);
        if (!is_null($itemValues)) {
            $values = array();
            foreach ($itemValues as $itemName => $item) {
                $values[$name . '_' . $itemName] = $item;
            }
            $this->setValues($values);
        }
    }

    /**
     * Přejmenuje vložené checkboxy pro použití ve formuláři
     *
     * @param EaseHtmlCheckboxTag $pageItem     vkládaný objekt CheckBoxu
     * @param string              $pageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return EaseHtmlCheckboxTag
     */
    function &addItem($pageItem, $pageItemName = null)
    {
        /**
         * Allready Added Item
         * @var EaseHtmlCheckboxTag
         */
        $itemInpage = parent::addItem($pageItem);
        if (is_object($itemInpage)) {
            if (isset($this->items)) {
                $keys = array_keys($this->items);
                $itemInpage->setTagProperties(array('name' => $itemInpage->getTagProperty('name') . '#' . $keys[$this->_subitemCount]));
                if (isset($this->values[$keys[$this->_subitemCount]])) {
                    $itemInpage->setValue((bool) $this->values[$keys[$this->_subitemCount]]);
                }
                next($this->items);
                $this->_subitemCount++;
            }
        }

        return $itemInpage;
    }

    /**
     * Vložení jména skupiny
     */
    public function finalize()
    {
        parent::finalize();
        parent::addItem(new EaseHtmlInputHiddenTag('CheckBoxGroups[' . $this->name . ']', $this->getTagName()));
    }

    /**
     * Poprvé nastaví hodnotu checkboxu. Druhé volání nastavuje příznak checked,
     * pokud je hodnota stejná jako již nabitá
     *
     * @param string $value vracená hodnota
     *
     * @todo boolean $Automatic zabraňuje mazání textu z tlačítek v objektu SubmitButton
     */
    public function setValue($value)
    {
        $CurrentValue = $this->GetTagProperty('value');
        if ($CurrentValue) {
            if ($CurrentValue == $value) {
                $this->setTagProperties(array('checked'));
            }
        } else {
            $this->setTagProperties(array('value' => $value));
        }
    }

    /**
     * Nastaví hodnoty položek
     *
     * @param array $Values pole hodnot
     */
    public function setValues($Values)
    {
        $TagName = $this->getTagName();
        foreach (array_keys($this->items) as $ItemKey) {
            if (isset($Values[$TagName . '_' . $ItemKey])) {
                $this->values[$ItemKey] = $Values[$TagName . '_' . $ItemKey];
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
     * @param string $name  jméno tagu
     * @param string $value předvolená hodnota
     */
    public function __construct($name, $value = null)
    {
        parent::__construct($name, $value);
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
     * @param string $name  jméno tagu
     * @param string $value předvolené heslo
     */
    public function __construct($name, $value = null)
    {
        parent::__construct($name, $value);
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
     * @param string $name       jméno tagu
     * @param bool   $checked    stav checkboxu
     * @param string $value      vracená hodnota checkboxu
     * @param array  $properties parametry tagu
     */
    public function __construct($name, $checked = false, $value = null, $properties = null)
    {
        if ($properties) {
            $properties['type'] = 'checkbox';
        } else {
            $properties = array('type' => 'checkbox');
        }
        if ($checked) {
            $properties['checked'] = 'true';
        }
        if ($value) {
            $properties['value'] = $value;
        }
        if ($name != '') {
            $properties['name'] = $name;
        }
        $this->setTagProperties($properties);
        parent::__construct($name);
    }

    /**
     * Nastaví zaškrtnutí
     *
     * @param boolean $value nastavuje parametr "checked" tagu
     */
    public function setValue($value = true)
    {
        if ($value) {
            $this->setTagProperties(array('checked' => 'true'));
        } else {
            unset($this->tagProperties['checked']);
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
     * <input type="submit" name="$label" value="$value" title="$Hint">
     *
     * @param string $label    nápis na tlačítku
     * @param string $value    odesílaná hodnota
     * @param string $Hint     tip při najetí myší
     * @param string $classCss css třída pro tag tlačítka
     */
    public function __construct($label, $value = null, $Hint = null, $classCss = null)
    {
        $properties = array('type' => 'submit');
        if (!$value) {
            $value = trim(str_replace(array(' ', '?'), '', @iconv("utf-8", "us-ascii//TRANSLIT", strtolower($label))));
        } else {
            $properties['value'] = $value;
        }
        if ($Hint) {
            $properties['title'] = $Hint;
        }
        if ($classCss) {
            $properties['class'] = $classCss;
        }
        $this->setTagProperties($properties);
        parent::__construct($value, $label);
        $this->Label = $label;
    }

    /**
     * Nastaví hodnotu
     *
     * @param string  $value     vracená hodnota tagu
     * @param boolean $Automatic Hack pro zachování labelů při plnění formuláře
     */
    public function setValue($value, $Automatic = false)
    {
        if (!$Automatic) { //FillUp nenastavuje Labely tlačítek
            parent::SetValue($value);
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
     * @param string $value vracená hodnota
     * @param string $Hint  text tipu
     */
    public function __construct($Image, $Label, $value = null, $Hint = null)
    {
        $Properties = array('type' => 'image');
        if (!$value) {
            $value = trim(str_replace(array(' ', '?'), '', @iconv("utf-8", "us-ascii//TRANSLIT", strtolower($Label))));
        } else {
            $Properties['value'] = $value;
        }
        if ($Hint) {
            $Properties['title'] = $Hint;
        }
        $Properties['src'] = $Image;
        $this->setTagProperties($Properties);
        parent::__construct($value, $Label);
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
     * @param string $name       jméno tagu
     * @param string $value      vracená hodnota
     * @param array  $properties Pole vlastností tagu
     */
    public function __construct($name, $value = null, $properties = null)
    {
        if (!$value) {
            $value = $name;
        }
        if (is_null($properties)) {
            $properties = array();
        }
        $properties['type'] = 'submit';
        $properties['name'] = $name;
        $properties['value'] = $value;
        parent::__construct($name, $value, $properties);
    }

    /**
     * Maketa kuli popisku
     *
     * @param bool $value je ignorováno
     */
    public function setValue($value = true)
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
    public $content = null;

    /**
     *
     */
    public $setName = true;

    /**
     * Textarea
     *
     * @param string $name       jméno tagu
     * @param string $content    obsah textarey
     * @param array  $properties vlastnosti tagu
     */
    public function __construct($name, $content = '', $properties = null)
    {
        $this->setTagName($name);
        parent::__construct('textarea', $properties);
        if ($content) {
            $this->addItem($content);
        }
    }

    /**
     * Nastaví obsah
     *
     * @param string $value hodnota
     */
    public function setValue($value)
    {
        $this->pageParts = array();
        $this->addItem($value);
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
    public $value = null;

    /**
     * Tag položky rozbalovací nabídky
     *
     * @param string|mixed $content text volby
     * @param string|int   $value   vracená hodnota
     */
    public function __construct($content, $value = null)
    {
        parent::__construct('option', array('value' => $value), $content);
        $this->setObjectName($this->getObjectName() . '@' . $value);
        $this->Value = &$this->tagProperties['value'];
    }

    /**
     * Nastaví předvolenou položku
     */
    public function setDefault()
    {
        return $this->setTagProperties(array('selected'));
    }

    /**
     * Nastaví hodnotu
     *
     * @param int|string $value vracená hodnota
     */
    public function setValue($value)
    {
        $this->Value = $value;
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
    public $defaultValue = null;

    /**
     * Automaticky nastavovat název elemnetu
     * @var boolean
     */
    public $setName = true;

    /**
     * @var pole hodnot k nabídnutí selectu
     */
    public $items = array();

    /**
     * Mají se vloženým položkám nastavovat ID ?
     * @var boolean
     */
    private $_itemsIDs = false;

    /**
     * Html select box
     *
     * @param string $name         jmeno
     * @param array  $items        polozky
     * @param mixed  $defaultValue id predvolene polozky
     * @param array  $itemsIDs     id položek
     * @param array  $properties   tag properties
     */
    public function __construct($name, $items = null, $defaultValue = null, $itemsIDs = false, $properties = null)
    {
        parent::__construct('select', $properties);
        $this->defaultValue = $defaultValue;
        $this->_itemsIDs = $itemsIDs;
        $this->setTagName($name);
        if (is_array($items)) {
            $this->addItems($items);
        }
    }

    /**
     * Hromadné vložení položek
     *
     * @param array $items položky výběru
     */
    public function addItems($items)
    {
        foreach ($items as $itemName => $itemValue) {
            $NewItem = $this->addItem(new EaseHtmlOptionTag($itemValue, $itemName));
            if ($this->_itemsIDs) {
                $NewItem->setTagID($this->getTagName() . $itemName);
            }
            if ($this->defaultValue == $itemName) {
                $this->lastItem->setDefault();
            }
        }
    }

    /**
     * Vloží hodnotu
     *
     * @param string $value   hodnota
     * @param string $valueID id hodnoty
     */
    public function addValue($value, $valueID = 0)
    {
        $this->addItems(array($valueID => $value));
    }

    /**
     * Maketa načtení položek
     *
     * @return array
     */
    public function loadItems()
    {
        return array();
    }

    /**
     * Nastavení hodnoty
     *
     * @param string $value nastavovaná hodnota
     */
    public function setValue($value)
    {
        if (trim(strlen($value))) {
            foreach ($this->pageParts as $option) {
                if ($option->value == $value) {
                    $option->setDefault();
                } else {
                    unset($option->tagProperties['selected']);
                }
            }
        } else {
            if (isset($this->pageParts) && count($this->pageParts)) {
                $firstItem = &reset($this->pageParts);
                $firstItem->setDefault();
            }
        }
    }

    /**
     * Vložit načtené položky
     */
    public function finalize()
    {
        if (!count($this->pageParts)) { //Uninitialised Select - so we load items
            $this->addItems($this->loadItems());
        }
    }

    /**
     * Odstarní položku z nabídky
     *
     * @param string $itemID klíč hodnoty k odstranění ze seznamu
     */
    public function delItem($itemID)
    {
        unset($this->pageParts['EaseHtmlOptionTag@' . $itemID]);
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
    public $formTarget = null;

    /**
     * Metoda odesílání
     * @var string GET|POST
     */
    public $formMethod = null;

    /**
     * Nastavovat formuláři jméno ?
     * @var type
     */
    public $setName = false;

    /**
     * Zobrazí html formulář
     *
     * @param string $formName      jméno formuláře
     * @param string $formAction    cíl formulář např login.php
     * @param string $formMethod    metoda odesílání POST|GET
     * @param mixed  $formContents  prvky uvnitř formuláře
     * @param array  $tagProperties vlastnosti tagu například:
     *                              array('enctype' => 'multipart/form-data')
     */
    public function __construct($formName, $formAction = null, $formMethod = 'post', $formContents = null, $tagProperties = null)
    {
        parent::__construct('form', array('method' => $formMethod, 'name' => $formName));
        if ($formAction) {
            $this->setFormTarget($formAction);
        } else {
            $this->setFormTarget($_SERVER['REQUEST_URI']);
        }
        if (isset($formContents)) {
            $this->addItem($formContents);
        }
        if (!is_null($tagProperties)) {
            $this->setTagProperties($tagProperties);
        }
    }

    /**
     * Nastaví cíl odeslání
     *
     * @param string $formTarget cíl odeslání formuláře
     */
    public function setFormTarget($formTarget)
    {
        $this->formTarget = $formTarget;
        $this->setTagProperties(array('action' => $formTarget));
    }

    /**
     * Změní jeden nebo více parametrů v ACTION url formuláře
     *
     * @param array $parametersToChange pole parametrů
     * @param bool  $replace            přepisovat již existující
     */
    public function changeActionParameter($parametersToChange, $replace = true)
    {
        if (is_array($parametersToChange) && count($parametersToChange)) {
            foreach ($parametersToChange as $paramName => $paramValue) {
                if ($paramValue == true) {
                    unset($parametersToChange[$paramName]);
                }
            }
            $targetParts = explode('&', str_replace('&&', '&', str_replace('?', '&', $this->formTarget)));
            if (is_array($targetParts) && count($targetParts)) {
                $formTargetComputed = '';
                $targetPartsValues = array();
                foreach ($targetParts as $targetPart) {
                    if (!strstr($targetPart, '=')) {
                        $formTargetComputed .= $targetPart;
                        continue;
                    }
                    list($targetPartName, $targetPartaValue) = explode('=', $targetPart);
                    if ($targetPartValue == true) {
                        continue;
                    }
                    $targetPartsValues[$targetPartName] = $targetPartValue;
                }
            }
            if ($replace) {
                $newTargPartVals = array_merge($targetPartsValues, $parametersToChange);
            } else {
                $newTargPartVals = array_merge($parametersToChange, $targetPartsValues);
            }
            $glueSign = '?';
            foreach ($newTargPartVals as $newTargetPartsValName => $newTargetPartsValue) {
                $formTargetComputed .= $glueSign . urlencode($newTargetPartsValName) . '=' . urlencode($newTargetPartsValue);
                $glueSign = '&';
            }
            $this->setFormTarget($formTargetComputed);
        }
    }

    /**
     * Pokusí se najít ve vložených objektech tag zadaného jména
     *
     * @param string        $searchFor jméno hledaného elementu
     * @param EaseContainer $where     objekt v němž je hledáno
     *
     * @return EaseContainer|class
     */
    function & objectContentSearch($searchFor, $where = null)
    {
        if (is_null($where)) {
            $where = & $this;
        }
        $itemFound = null;
        if (isset($where->pageParts) && is_array($where->pageParts) && count($where->pageParts)) {
            foreach ($where->pageParts as $pagePart) {
                if (is_object($pagePart)) {
                    if (method_exists($pagePart, 'GetTagName')) {
                        if ($pagePart->getTagName() == $searchFor) {
                            return $pagePart;
                        }
                    } else {
                        $itemFound = $this->objectContentSearch($searchFor, $pagePart);
                        if ($itemFound) {
                            return $itemFound;
                        }
                    }
                }
            }
        }

        return $itemFound;
    }

    /**
     * Doplnění perzistentních hodnot
     */
    public function finalize()
    {
        $this->setupWebPage();
        if (isset($this->webPage->requestValuesToKeep) && is_array($this->webPage->requestValuesToKeep) && count($this->webPage->requestValuesToKeep)) {
            foreach ($this->webPage->requestValuesToKeep as $name => $value) {
                if (!$this->objectContentSearch($name)) {
                    if (is_string($value)) {
                        $this->addItem(new EaseHtmlInputHiddenTag($name, $value));
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
     * @param string $for        vztažný element
     * @param mixed  $contents   obsah opatřovaný popiskem
     * @param array  $properties vlastnosti tagu
     */
    public function __construct($for, $contents = null, $properties = null)
    {
        $this->setTagProperties(array('for' => $for));
        parent::__construct('label', $properties);
        $this->Contents = $this->addItem($contents);
    }

    /**
     * Nastaví jméno objektu
     *
     * @param string $objectName nastavované jméno
     *
     * @return string New object name
     */
    public function setObjectName($objectName = null)
    {
        if ($objectName) {
            return parent::setObjectName($objectName);
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
    public $itemClass = 'EaseHtmlInputTag';

    /**
     * Objekt olabelovaný
     * @var EaseHtmlInputTag
     */
    public $enclosedElement = null;

    /**
     *
     * @var type
     */
    public $labelElement = null;

    /**
     * Typ Tagu
     * @var string
     */
    public $tagType = null;

    /**
     * obecný input opatřený patřičným popiskem
     *
     * @param string $name       jméno
     * @param string $value      hondnota
     * @param string $label      popisek
     * @param array  $properties vlastnosti tagu
     */
    public function __construct($name, $value = null, $label = null, $properties = null)
    {
        parent::__construct();
        if (!isset($properties['id'])) {
            $properties['id'] = $name . 'TextInput';
        }
        if ($label) {
            $this->addCSS(
                '.InputCaption {
cursor: pointer;
display: block;
font-size: x-small;
margin-top: 5px;}'
            );
            $this->labelElement = new EaseHtmlLabelTag($properties['id'], $label, array('class' => 'InputCaption'));
        }

        switch ($this->itemClass) {
            case 'EaseHtmlCheckboxTag':
                $this->enclosedElement = new $this->itemClass($name, $value, 'on', $properties);
                break;
            case 'EaseHtmlSelect':
                //function __construct($name, $items = null, $DefaultValue = null, $ItemsIDs = false, $Properties = null)
                $this->enclosedElement = new $this->itemClass($name, null, $value, false, $properties);
                break;
            default:
                $this->enclosedElement = new $this->itemClass($name, $value, $properties);
                break;
        }
    }

    /**
     * Seskládání
     */
    public function finalize()
    {
        $this->tagType = $this->enclosedElement->tagType;
        $this->addItem($this->labelElement);
        $this->addItem($this->enclosedElement);
    }

    /**
     * Vrací parametry obsaženého tagu
     *
     * @return string
     */
    public function getTagProperties()
    {
        if (method_exists($this->enclosedElement, 'getTagProperties')) {
            return $this->enclosedElement->getTagProperties();
        }
    }

    /**
     * Nastaví ID třídu obsaženého tagu
     *
     * @return string
     */
    public function setTagID($id)
    {
        if (method_exists($this->enclosedElement, 'setTagID')) {
            return $this->enclosedElement->setTagID();
        }
    }

    /**
     * Nastaví css třídu obsaženého tagu
     *
     * @return string
     */
    public function setTagClass($class)
    {
        if (method_exists($this->enclosedElement, 'setTagClass')) {
            return $this->enclosedElement->setTagClass($class);
        }
    }

    /**
     * Vrací css třídu obsaženého tagu
     *
     * @return string
     */
    public function getTagClass()
    {
        if (method_exists($this->enclosedElement, 'getTagClass')) {
            return $this->enclosedElement->getTagClass();
        }
    }

    /**
     * Vrací hodnotu parametru vloženého tagu
     *
     * @param string $propertyName název parametru
     *
     * @return string|int|null hodnota parametru
     */
    public function getTagProperty($propertyName)
    {
        if (method_exists($this->enclosedElement, 'getTagProperties')) {
            return $this->enclosedElement->getTagProperty($propertyName);
        }
    }

    /**
     * Vrací jméno vloženého tagu
     *
     * @return string|null
     */
    public function getTagName()
    {
        if (method_exists($this->enclosedElement, 'getTagName')) {
            return $this->enclosedElement->getTagName();
        }
    }

    /**
     * Nastaví hodnotu vloženého tagu
     *
     * @param  int|string $value
     * @return null
     */
    public function setValue($value)
    {
        if (method_exists($this->enclosedElement, 'setValue')) {
            return $this->enclosedElement->setValue($value);
        }

        return null;
    }

    /**
     * Nastaví pole hodnot vloženému tagu např. poli checkboxů
     *
     * @param  int|string $values
     * @return null
     */
    public function setValues($values)
    {
        if (method_exists($this->enclosedElement, 'setValues')) {
            return $this->enclosedElement->setValues($values);
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
    public $itemClass = 'EaseHtmlInputTextTag';

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
    public $itemClass = 'EaseHtmlInputSearchTag';

    /**
     *
     */
    public function setDataSource($dataSource)
    {
        $this->enclosedElement->setDataSource($dataSource);
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
    public $itemClass = 'EaseHtmlInputFileTag';

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
    public $itemClass = 'EaseHtmlTextareaTag';

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
    public $itemClass = 'EaseHtmlInputPasswordTag';

}
