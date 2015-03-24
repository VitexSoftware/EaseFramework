<?php

/**
 * Classy pro generování HTML
 *
 * @package    EaseFrameWork
 * @subpackage EaseHtml
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 */
require_once 'EasePage.php';

/**
 * Common HTML tag class
 *
 * @subpackage EaseHtml
 * @author     Vitex <vitex@hippy.cz>
 */
class EaseHtmlTag extends EasePage
{

    /**
     * Jméno tagu - je použit i jako jméno objektu
     * @var string
     */
    public $tagName = null;

    /**
     * Typ tagu - např A či STRONG
     * @var string
     */
    public $tagType = null;

    /**
     * Pole vlastností tagu
     * @var array
     */
    public $tagProperties = null;

    /**
     * pole ze kterého se rendruje obsah STYLE tagu
     * @var array
     */
    public $cssProperties = null;

    /**
     * Nelogovat události HTML objektů
     * @var string
     */
    public $logType = 'none';

    /**
     * Koncové lomítko pro xhtml
     * @var string
     */
    public $trail = ' /';

    /**
     * Má si objekt automaticky plnit vlastnost name ?
     */
    public $setName = false;

    /**
     * Objekt pro vykreslení obecného nepárového html tagu
     *
     * @param string       $tagType       typ tagu
     * @param array|string $tagProperties parametry tagu
     * @param mixed        $content       vkládaný obsah
     */
    public function __construct($tagType = null, $tagProperties = null, $content = null)
    {
        if (is_null($tagType)) {
            $tagType = $this->tagType;
        } else {
            $this->setTagType($tagType);
        }
        parent::__construct();
        if ($tagProperties) {
            $this->setTagProperties($tagProperties);
        }
        if ($content) {
            $this->addItem($content);
        }
    }

    /**
     * Nastaví jméno objektu
     *
     * @param string $objectName jméno objektu
     *
     * @return string New object name
     */
    public function setObjectName($objectName = null)
    {
        if ($objectName) {
            return parent::setObjectName($objectName);
        }
        if ($this->tagName) {
            return parent::setObjectName(get_class($this) . '@' . $this->tagName);
        } else {
            if ($this->tagType) {
                return parent::setObjectName(get_class($this) . '@' . $this->tagType);
            } else {
                return parent::setObjectName();
            }
        }
    }

    /**
     * Nastaví jméno tagu
     *
     * @param string $tagName jméno tagu do vlastnosti NAME
     */
    public function setTagName($tagName)
    {
        $this->tagName = $tagName;
        if ($this->setName) {
            $this->tagProperties['name'] = $tagName;
        }
        $this->setObjectName();
    }

    /**
     * Returns name of tag
     *
     * @return string
     */
    public function getTagName()
    {
        if ($this->setName) {
            if (isset($this->tagProperties['name'])) {
                return $this->tagProperties['name'];
            } else {
                return NULL;
            }
        } else {
            return $this->tagName;
        }
    }

    /**
     * Nastaví typ tagu
     *
     * @param string $tagType typ tagu - např. img
     */
    public function setTagType($tagType)
    {
        $this->tagType = $tagType;
    }

    /**
     * Vrací typ tagu
     *
     * @return string typ tagu - např. img
     */
    public function getTagType()
    {
        return $this->tagType;
    }

    /**
     * Nastaví classu tagu
     *
     * @param string $className jméno css třídy
     */
    public function setTagClass($className)
    {
        $this->setTagProperties(array('class' => $className));
    }

    /**
     * Přidá classu tagu
     *
     * @param string $className jméno css třídy
     */
    public function addTagClass($className)
    {
        $this->setTagClass($this->getTagClass() . ' ' . $className);
    }

    /**
     * Vrací css classu tagu
     */
    public function getTagClass()
    {
        return $this->getTagProperty('class');
    }

    /**
     * Nastaví tagu zadane id, nebo vygenerované náhodné
     *
     * @param string $tagID #ID html tagu pro JavaScript a Css
     *
     * @return string nastavené ID
     */
    public function setTagID($tagID = null)
    {
        if (is_null($tagID)) {
            $this->setTagProperties(array('id' => EaseBrick::randomString()));
        } else {
            $this->setTagProperties(array('id' => $tagID));
        }

        return $this->getTagID();
    }

    /**
     * Vrací ID html tagu
     *
     * @return string
     */
    public function getTagID()
    {
        if (isset($this->tagProperties['id'])) {
            return $this->tagProperties['id'];
        } else {
            return null;
        }
    }

    /**
     * Returns property tag value
     *
     * @param string $propertyName název vlastnosti tagu. např. "src" u obrázku
     *
     * @return string current tag property value
     */
    public function getTagProperty($propertyName)
    {
        if (isset($this->tagProperties[$propertyName])) {
            return $this->tagProperties[$propertyName];
        }

        return null;
    }

    /**
     * Nastaví paramatry tagu
     *
     * @param mixed $tagProperties asociativní pole parametrů tagu
     */
    public function setTagProperties($tagProperties)
    {
        if (is_array($tagProperties)) {
            if (isset($tagProperties['id'])) {
                $tagProperties['id'] = preg_replace("/[^A-Za-z0-9_\-]/", '', $tagProperties['id']);
            }
            if (is_array($this->tagProperties)) {
                $this->tagProperties = array_merge($this->tagProperties, $tagProperties);
            } else {
                $this->tagProperties = $tagProperties;
            }
            if (isset($tagProperties['name'])) {
                $this->setTagName($tagProperties['name']);
            }
        } else {
            $propBuff = $tagProperties;
            //if (substr($propBuff, 0, 1) != ' ') $propBuff = ' ' . $tagProperties;
            $this->tagProperties = ' ' . $propBuff;
        }
    }

    /**
     * Vrátí parametry tagu jako řetězec
     *
     * @param mixed $tagProperties asociativní pole parametrú nebo řetězec
     *
     * @return string
     */
    public function tagPropertiesToString($tagProperties = null)
    {
        if (!$tagProperties) {
            $tagProperties = $this->tagProperties;
        }
        if (is_array($tagProperties)) {
            $TagPropertiesString = ' ';
            foreach ($tagProperties as $TagPropertyName => $TagPropertyValue) {
                if ($TagPropertyName) {
                    if (is_numeric($TagPropertyName)) {
                        if (!strstr($TagPropertiesString, ' ' . $TagPropertyValue . ' ')) {
                            $TagPropertiesString .= ' ' . $TagPropertyValue . ' ';
                        }
                    } else {
                        $TagPropertiesString .= $TagPropertyName . '="' . $TagPropertyValue . '" ';
                    }
                } else {
                    $TagPropertiesString .= $TagPropertyValue . ' ';
                }
            }

            return $TagPropertiesString;
        } else {
            return $this->tagProperties;
        }
    }

    /**
     * Nastaví paramatry Css
     *
     * @param array|string $cssProperties asociativní pole, nebo CSS definice
     */
    public function setTagCss($cssProperties)
    {
        if (is_array($cssProperties)) {
            if (is_array($this->cssProperties)) {
                $this->cssProperties = array_merge($this->cssProperties, $cssProperties);
            } else {
                $this->cssProperties = $cssProperties;
            }
        } else {
            $propBuff = $cssProperties;
            //if (substr($propBuff, 0, 1) != ' ') $propBuff = ' ' . $cssProperties;
            $this->cssProperties = ' ' . $propBuff;
        }
        $this->setTagProperties(array('style' => $this->cssPropertiesToString()));
    }

    /**
     * Vrátí parametry Cssu jako řetězec
     *
     * @param array|string $cssProperties pole vlastností nebo CSS definice
     *
     * @return string
     */
    public function cssPropertiesToString($cssProperties = null)
    {
        if (!$cssProperties) {
            $cssProperties = $this->cssProperties;
        }
        if (is_array($cssProperties)) {
            $cssPropertiesString = ' ';
            foreach ($cssProperties as $CssPropertyName => $CssPropertyValue) {
                $cssPropertiesString .= $CssPropertyName . ':' . $CssPropertyValue . ';';
            }

            return $cssPropertiesString;
        } else {
            return $this->cssProperties;
        }
    }

    /**
     * Vykreslí tag
     */
    public function draw()
    {
        echo "\n<" . $this->tagType;
        echo $this->tagPropertiesToString();
        echo $this->trail;
        echo '>';
    }

}

/**
 * Obecný párový HTML tag
 *
 * @subpackage EaseHtml
 * @author     Vitex <vitex@hippy.cz>
 */
class EaseHtmlPairTag extends EaseHtmlTag
{

    /**
     * Character to close tag
     * @var type
     */
    public $trail = '';

    /**
     * Render tag and its contents
     */
    public function draw()
    {
        $this->tagBegin();
        $this->drawAllContents();
        $this->tagEnclousure();
    }

    /**
     * Zobrazí počátek párového tagu
     */
    public function tagBegin()
    {
        parent::draw();
    }

    /**
     * Zobrazí konec párového tagu
     */
    public function tagEnclousure()
    {
        echo '</' . $this->tagType . ">\n";
    }

}

/**
 * IMG tag class
 *
 * @subpackage EaseHtml
 * @author     Vitex <vitex@hippy.cz>
 */
class EaseHtmlImgTag extends EaseHtmlTag
{

    /**
     * Html Obrazek
     *
     * @param string $image         url obrázku
     * @param string $hint          hint při nájezu myší
     * @param int    $width         šířka v pixelech
     * @param int    $height        výška v pixelech
     * @param array  $tagProperties ostatni nastaveni tagu
     */
    public function __construct($image, $hint = null, $width = null, $height = null, $tagProperties = null)
    {
        if (is_null($tagProperties)) {
            $tagProperties = array();
        }
        $tagProperties['src'] = $image;
        if (isset($hint)) {
            $tagProperties['title'] = $hint;
        }
        if (isset($width)) {
            $tagProperties['width'] = $width;
        }
        if (isset($height)) {
            $tagProperties['height'] = $height;
        }
        parent::__construct('img', $tagProperties);
    }

}

/**
 * HTML Paragraph class tag
 *
 * @subpackage EaseHtml
 * @author     Vitex <vitex@hippy.cz>
 */
class EaseHtmlPTag extends EaseHtmlPairTag
{

    /**
     * Odstavec
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('p', $properties, $content);
    }

}

/**
 * HTML Table cell class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlTdTag extends EaseHtmlPairTag
{

    /**
     * Buňka tabulky
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('td', $properties, $content);
    }

}

/**
 * HTML Table Header cell class
 *
 * @subpackage EaseHtml
 * @author     Vitex <vitex@hippy.cz>
 */
class EaseHtmlThTag extends EaseHtmlPairTag
{

    /**
     * Buňka s popiskem tabulky
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('th', $properties, $content);
    }

}

/**
 * HTML Table row class
 *
 * @subpackage EaseHtml
 * @author     Vitex <vitex@hippy.cz>
 */
class EaseHtmlTrTag extends EaseHtmlPairTag
{

    /**
     * TR tag
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('tr', $properties, $content);
    }

}

class EaseHtmlThead extends EaseHtmlPairTag
{

    /**
     * <thead>
     * @param mixed $content
     * @param array $properties
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('thead', $properties, $content);
    }

}

class EaseHtmlTbody extends EaseHtmlPairTag
{

    /**
     * <tbody>
     * @param mixed $content
     * @param array $properties
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('tbody', $properties, $content);
    }

}

/**
 * HTML table
 *
 * @subpackage EaseHtml
 * @author     Vitex <vitex@hippy.cz>
 */
class EaseHtmlTableTag extends EaseHtmlPairTag
{

    /**
     * Hlavička tabulky
     * @var EaseHtmlThead
     */
    public $tHead = null;

    /**
     * Tělo tabulky
     * @var EaseHtmlTbody
     */
    public $tbody = null;

    /**
     * Html Tabulka
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('table', $properties, $content);
        $this->tHead = $this->addItem(new EaseHtmlThead);
        $this->tBody = $this->addItem(new EaseHtmlTbody);
    }

    /**
     * @param array $headerColumns položky záhlaví tabulky
     */
    public function setHeader($headerColumns)
    {
        $this->tHead->emptyContents();
        $this->addRowHeaderColumns($headerColumns);
    }

    /**
     * Vloží do tabulky obsah pole jako buňky
     *
     * @param array $columns    pole obsahů buňek
     * @param array $properties pole vlastností dané všem buňkám
     *
     * @return EaseHtmlTrTag odkaz na řádku tabulky
     */
    function & addRowColumns($columns = null, $properties = null)
    {
        $tableRow = $this->tBody->addItem(new EaseHtmlTrTag());
        if (is_array($columns)) {
            foreach ($columns as $column) {
                if (is_object($column) && method_exists($column, 'getTagType') && ($column->getTagType() == 'td')) {
                    $tableRow->addItem($column);
                } else {
                    $tableRow->addItem(new EaseHtmlTdTag($column, $properties));
                }
            }
        }

        return $tableRow;
    }

    /**
     * Vloží do tabulky obsah pole jako buňky
     *
     * @param array $columns    pole obsahů buňek
     * @param array $properties pole vlastností dané všem buňkám
     *
     * @return EaseHtmlTrTag odkaz na řádku tabulky
     */
    function & addRowHeaderColumns($columns = null, $properties = null)
    {
        $tableRow = $this->tHead->addItem(new EaseHtmlTrTag());
        if (is_array($columns)) {
            foreach ($columns as $column) {
                if (is_object($column) && method_exists($column, 'getTagType') && ($column->getTagType() == 'th')) {
                    $tableRow->addItem($column);
                } else {
                    $tableRow->addItem(new EaseHtmlThTag($column, $properties));
                }
            }
        }

        return $tableRow;
    }

    /**
     * Je tabulka prázdná ?
     *
     * @param null $element je zde pouze z důvodu zpětné kompatibility
     * @return type
     */
    function isEmpty($element = null)
    {
        return $this->tBody->isEmpty();
    }

    /**
     * Naplní tabulku daty
     *
     * @param array $contents
     */
    function populate($contents)
    {
        foreach ($contents as $cRow) {
            $this->addRowColumns($cRow);
        }
    }

}

/**
 * Třída pro tělo HTML stránky
 *
 * @subpackage EaseHtml
 * @author     Vitex <vitex@hippy.cz>
 */
class EaseHtmlBodyTag extends EaseHtmlPairTag
{

    /**
     * Tělo stránky je v aplikaci vždy dostupně jako
     * $this->easeShared->webPage->body
     *
     * @param string $TagID   id tagu
     * @param mixed  $Content vkládané prvky
     */
    public function __construct($TagID = null, $Content = null)
    {
        parent::__construct('body', null, $Content);
        if (!is_null($TagID)) {
            $this->setTagID($TagID);
        }
    }

    /**
     * Nastaví jméno objektu na "body"
     *
     * @param string $ObjectName jméno objektu
     */
    public function setObjectName($ObjectName = null)
    {
        parent::setObjectName('body');
    }

}

/**
 * HTML top tag class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlHtmlTag extends EaseHtmlPairTag
{

    public $LangCode = 'cs-CZ';

    /**
     * HTML
     *
     * @param mixed $Content vložený obsah - tělo stránky
     */
    public function __construct($Content = null)
    {
        parent::__construct('html', array('lang' => $this->langCode, 'xmlns' => 'http://www.w3.org/1999/xhtml', 'xml:lang' => $this->langCode), $Content);
    }

    /**
     * Nastaví jméno objektu na "html"
     *
     * @param string $ObjectName jméno objektu
     */
    public function setObjectName($ObjectName = null)
    {
        parent::setObjectName('html');
    }

    /*
      function &addItem($PageItem) {
      if (($PageItem->getObjectName()=='head')||($PageItem->getObjectName()=='body')) {
      $ItemAdded = parent::addItem($PageItem);
      } else {
      $ItemAdded = parent::addItem(new EaseHtmlBodyTag(null,$PageItem));
      }

      return $ItemAdded;
      }
     */
}

/**
 * Siple HTML head tag class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlSimpleHeadTag extends EaseHtmlPairTag
{

    /**
     * Content type of webpage
     * @var string
     */
    public static $ContentType = 'text/html';

    /**
     * head tag with defined meta http-equiv content type
     *
     * @param mixed $Contents   vkládaný obsah
     * @param array $Properties parametry tagu
     */
    public function __construct($Contents = null, $Properties = null)
    {
        parent::__construct('head', $Properties, $Contents);
        $this->addItem('<meta http-equiv="Content-Type" content="' . self::$ContentType . '; charset=' . $this->charSet . '" />');
    }

}

/**
 * HTML title class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlTitleTag extends EaseHtmlPairTag
{

    /**
     * Title html tag
     *
     * @param string $Contents   text titulku
     * @param array  $Properties parametry tagu
     */
    public function __construct($Contents = null, $Properties = null)
    {
        parent::__construct('title', $Properties, $Contents);
    }

}

/**
 * HTML webPage head class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlHeadTag extends EaseHtmlPairTag
{

    /**
     * Javascripts to render in page
     * @var array
     */
    public $javaScripts = null;

    /**
     * Css definitions
     * @var strig
     */
    public $cascadeStyles = null;

    /**
     * Html HEAD tag with basic contents and skin support
     *
     * @param mixed $Content vkládaný obsah
     */
    public function __construct($Content = null)
    {
        parent::__construct('head', null, $Content);
        $this->addItem('<meta http-equiv="Content-Type" content="text/html; charset=' . $this->charSet . '" />');
    }

    /**
     * Change name directly to head
     *
     * @param string $ObjectName jméno objektu
     */
    public function setObjectName($ObjectName = null)
    {
        parent::setObjectName('head');
    }

    /**
     * Vykreslení bloku scriptu
     *
     * @param string $JavaScript vkládaný skript
     *
     * @return string
     */
    public static function jsEnclosure($JavaScript)
    {
        return '
<script>
// <![CDATA[
' . $JavaScript . '
// ]]>
</script>
';
    }

    /**
     * Vloží do hlavíčky název stránky
     */
    public function finalize()
    {
        $this->addItem('<title>' . $this->webPage->pageTitle . '</title>');
    }

    /**
     * Vykreslí hlavičku HTML stránky
     */
    public function draw()
    {

        if (isset($this->easeShared->cascadeStyles) && count($this->easeShared->cascadeStyles)) {
            $cascadeStyles = array();
            foreach ($this->easeShared->cascadeStyles as $StyleRes => $Style) {
                if ($StyleRes == $Style) {
                    $this->addItem('<link href="' . $Style . '" rel="stylesheet" type="text/css" media="' . 'screen' . '" />'); //TODO: solve screen
                } else {
                    $cascadeStyles[] = $Style;
                }
            }
            $this->addItem('<style>' . implode("\n", $cascadeStyles) . '</style>');
        }

        if (isset($this->easeShared->javaScripts) && count($this->easeShared->javaScripts)) {
            ksort($this->easeShared->javaScripts, SORT_NUMERIC);

            $ODRStack = array();

            foreach ($this->easeShared->javaScripts as $Script) {
                $ScriptType = $Script[0];
                $ScriptBody = substr($Script, 1);
                switch ($ScriptType) {
                    case '#':
                        $this->addItem("\n" . '<script src="' . $ScriptBody . '"></script>');
//                      EaseShared::webPage()->body->addItem("\n".'<script type="text/javascript" src="' . $ScriptBody . '"></script>'); //TODO: rozchodit
                        break;
                    case '@':
                        $this->addItem(self::jsEnclosure($ScriptBody));
                        break;
                    case '$':
                        $ODRStack[] = $ScriptBody;
                        break;
                }
            }
            if (count($ODRStack)) {
                $this->addItem(
                    self::jsEnclosure('$(document).ready(function () { ' .
                        implode("\n", $ODRStack) . ' });')
                );
            }
        }
        parent::draw();
    }

}

/**
 * HTML hyperling class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlATag extends EaseHtmlPairTag
{

    /**
     * zobrazí HTML odkaz
     *
     * @param string $href       url odkazu
     * @param mixed  $contents   vkládaný obsah
     * @param array  $properties parametry tagu
     */
    public function __construct($href, $contents = null, $properties = null)
    {
        if (!is_array($properties)) {
            $properties = array();
        }
        if (!is_null($href)) {
            $properties['href'] = $href;
        }
        parent::__construct('a', $properties, $contents);
    }

    /**
     * Ošetření perzistentních hodnot
     */
    public function afterAdd()
    {
        if (isset($this->webPage->requestValuesToKeep) && is_array($this->webPage->requestValuesToKeep) && count($this->webPage->requestValuesToKeep)) {
            foreach ($this->webPage->requestValuesToKeep as $KeepName => $KeepValue) {
                if ($KeepValue == true) {
                    continue;
                }
                $Keep = urlencode($KeepName) . '=' . urlencode($KeepValue);
                if (!strstr($this->tagProperties['href'], urlencode($KeepName) . '=')) {
                    if (strstr($this->tagProperties['href'], '?')) {
                        $this->tagProperties['href'] .= '&' . $Keep;
                    } else {
                        $this->tagProperties['href'] .= '?' . $Keep;
                    }
                }
            }
        }
    }

}

/**
 * HTML list item tag class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlLiTag extends EaseHtmlPairTag
{

    /**
     * Simple LI tag
     *
     * @param mixed $ulContents obsah položky seznamu
     * @param array $properties parametry LI tagu
     */
    public function __construct($ulContents = null, $properties = null)
    {
        parent::__construct('li', $properties, $ulContents);
    }

}

/**
 * HTML unsorted list
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlUlTag extends EaseHtmlPairTag
{

    /**
     * Vytvori UL container
     *
     * @param mixed $ulContents položky seznamu
     * @param array $properties parametry tagu
     */
    public function __construct($ulContents = null, $properties = null)
    {
        parent::__construct('ul', $properties, $ulContents);
    }

    /**
     * Vloží pole elementů
     *
     * @param array $itemsArray pole hodnot nebo EaseObjektů s metodou draw()
     */
    public function addItems($itemsArray)
    {
        $itemsAdded = array();
        foreach ($itemsArray as $item) {
            $itemsAdded[] = $this->addItemSmart($item);
        }

        return $itemsAdded;
    }

    /**
     * Every item id added in EaseHtmlLiTag envelope
     *
     * @param mixed  $pageItem     obsah vkládaný jako položka výčtu
     * @param string $properties   Vlastnosti LI tagu
     *
     * @return mixed
     */
    function & addItemSmart($pageItem, $properties = null)
    {
        if (is_array($pageItem)) {
            foreach ($pageItem as $item) {
                $this->addItemSmart($item);
            }
            $itemAdded = & $this->lastItem;
        } else {
            if (isset($pageItem->tagType) && ($pageItem->tagType == 'li')) {
                $itemAdded = parent::addItem($pageItem);
            } else {
                $itemAdded = parent::addItem(new EaseHtmlLiTag($pageItem, $properties));
            }
        }

        return $itemAdded;
    }

}

/**
 * HTML unsorted list
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlOlTag extends EaseHtmlUlTag
{

    /**
     * Vytvori OL container
     *
     * @param mixed $ulContents položky seznamu
     * @param array $properties parametry tagu
     */
    function __construct($ulContents = null, $properties = null)
    {
        parent::__construct($ulContents, $properties);
        $this->setTagType('ol');
    }

}

/**
 * HTML major heading tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlStrongTag extends EaseHtmlPairTag
{

    /**
     * Tag pro tučné písmo
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('strong', $properties, $content);
    }

}

/**
 * HTML em tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlEmTag extends EaseHtmlPairTag
{

    /**
     * Tag kurzívu
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('em', $properties, $content);
    }

}

/**
 * HTML major heading tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlH1Tag extends EaseHtmlPairTag
{

    /**
     * Simple H1 Tag
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('h1', $properties, $content);
    }

}

/**
 * HTML H2 tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlH2Tag extends EaseHtmlPairTag
{

    /**
     * Nadpis druhé velikosti
     *
     * @param mixed  $content    text nadpisu
     * @param string $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('h2', $properties, $content);
    }

}

/**
 * HTML H3 tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlH3Tag extends EaseHtmlPairTag
{

    /**
     * Simple H3 tag
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('h3', $properties, $content);
    }

}

/**
 * HTML H4 tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlH4Tag extends EaseHtmlPairTag
{

    /**
     * Simple H4 tag
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('h4', $properties, $content);
    }

}

/**
 * HTML Div tag
 *
 * @deprecated since version 226
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlDivTag extends EaseHtmlPairTag
{

    /**
     * Prostý tag odstavce DIV
     *
     * @param string $name       ID tagu
     * @param mixed  $content    vložené prvky
     * @param array  $properties pole parametrů
     */
    public function __construct($name = null, $content = null, $properties = null)
    {
        if (!is_null($name)) {
            $this->setTagName($name);
            $this->setTagID($name);
        }
        parent::__construct('div', $properties, $content);
    }

}

/**
 * HTML Div tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlDiv extends EaseHtmlPairTag
{

    /**
     * Prostý tag odstavce DIV
     *
     * @param string $name       ID tagu
     * @param mixed  $content    vložené prvky
     * @param array  $properties pole parametrů
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('div', $properties, $content);
    }

}

/**
 * HTML5 Nav tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlNavTag extends EaseHtmlPairTag
{

    /**
     * Tag semantiky navigaze
     *
     * @param mixed $content    vložené prvky
     * @param array $properties pole parametrů
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('div', $properties, $content);
    }

}

/**
 * HTML span tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlSpanTag extends EaseHtmlPairTag
{

    /**
     * <span> tag
     *
     * @deprecated since version 226
     * @param string $name       jméno a ID tagu
     * @param mixed  $content    vkládaný obsah
     * @param array  $properties parametry tagu
     */
    public function __construct($name, $content = null, $properties = null)
    {
        if ($name) {
            $this->setTagName($name);
        }
        parent::__construct('span', $properties, $content);
    }

}

/**
 * HTML span tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlSpan extends EaseHtmlPairTag
{

    /**
     * <span> tag
     *
     * @param mixed  $content    vkládaný obsah
     * @param array  $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('span', $properties, $content);
    }

}

/**
 * Html Fieldset
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlFieldSet extends EaseHtmlPairTag
{

    /**
     * Legenda rámečku
     * @var mixed
     */
    public $Legend = null;

    /**
     * Objekt s tagem Legendy
     * @var EaseHtmlPairTag
     */
    public $LegendTag = null;

    /**
     * Obsah rámu
     * @var mixed
     */
    public $Content = null;

    /**
     * Zobrazí rámeček
     *
     * @param string|mixed $legend  popisek - text nebo Ease objekty
     * @param mixed        $content prvky vkládané do rámečku
     */
    public function __construct($legend, $content = null)
    {
        $this->setTagName($legend);
        $this->Legend = $legend;
        $this->LegendTag = $this->addItem(new EaseHtmlPairTag('legend', null, $this->Legend));
        if ($content) {
            $this->addItem($content);
        }
        parent::__construct('fieldset');
    }

    /**
     * Nastavení legendy
     *
     * @param string $legend popisek
     */
    public function setLegend($legend)
    {
        $this->Legend = $legend;
    }

    /**
     * Vložení legendy
     */
    public function finalize()
    {
        if ($this->Legend) {
            if (is_object(reset($this->pageParts))) {
                reset($this->pageParts)->pageParts = array($this->Legend);
            } else {
                array_unshift($this->pageParts, $this->LegendTag);
                reset($this->pageParts)->pageParts = array($this->Legend);
            }
        }
    }

}

/**
 * Fragment skriptu ve stránce
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseScriptTag extends EaseHtmlPairTag
{

    /**
     * Include JS code into page
     *
     * @param string $cData        vkládaná data
     * @param string $pageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return EaseScriptTag
     */
    function &addItem($cData, $pageItemName = null)
    {
        return parent::addItem("\n//<![CDATA[\n" . $cData . "\n// ]]>\n", $pageItemName);
    }

    /**
     * fragment skriptu ve stránce
     *
     * @param string $content text scriptu
     */
    public function __construct($content = '', $properties = NULL)
    {
        parent::__construct('script', $properties);
        if ($content) {
            $this->setTagName(md5($content));
            $this->addItem($content);
        }
    }

}

/**
 *  fragment skriptu ve stránce
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseJavaScript extends EaseScriptTag
{

    /**
     * fragment javaskriptu ve stránce
     *
     * @param string $content text scriptu
     */
    public function __construct($content, $properties = null)
    {
        if (is_null($properties)) {
            $properties = array('type' => 'text/javascript');
        } else {
            $properties['type'] = 'text/javascript';
        }
        parent::__construct($content, $properties);
    }

}

/**
 * HtmlParam tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlParamTag extends EaseHtmlTag
{

    /**
     * Paramm tag
     *
     * @param string $name  jméno parametru
     * @param string $value hodnota parametru
     */
    public function __construct($name, $value)
    {
        parent::__construct('param', array('name' => $name, 'value' => $value));
    }

}

/**
 * HTML Embed Tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlEmbedTag extends EaseHtmlTag
{

    /**
     * Clas for embeding object into webpage
     *
     * @param string  $src               cesta k zobrazovaným datům
     * @param string  $quality           kvalita zobrazení
     * @param string  $bgcolor           color name or code
     * @param int     $width             šířka v pixelech
     * @param int     $height            výška v pixelech
     * @param string  $name              název objektu
     * @param string  $align             zarovnání
     * @param boolean $allowScriptAccess povolit přístup ke scriptu
     * @param boolean $allowFullScreen   povolit celoobrazovkový režim
     * @param string  $type              typ vloženého obsahu
     * @param string  $pluginspage       adresa ke stažení obslužného pluginu
     */
    public function __construct($src, $quality, $bgcolor, $width, $height, $name, $align, $allowScriptAccess, $allowFullScreen, $type, $pluginspage)
    {
        parent::__construct(
            'embed', array(
          'src' => $src,
          'quality' => $quality,
          'bgcolor' => $bgcolor,
          'width' => $width,
          'height' => $height,
          'name' => $name,
          'align' => $align,
          'allowScriptAccess' => $allowScriptAccess,
          'allowFullScreen' => $allowFullScreen,
          'type' => $type,
          'pluginspage' => $pluginspage
            )
        );
    }

}

/**
 * Horizontal line tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlHrTag extends EaseHtmlTag
{

    /**
     * Horizontal line tag
     *
     * @param array $properties parametry tagu
     */
    public function __construct($properties = null)
    {
        parent::__construct('hr', $properties);
    }

}

/**
 * iFrame element
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlIframeTag extends EaseHtmlPairTag
{

    public $tagType = 'iframe';

    /**
     * iFrame element
     *
     * @param string $src        content url
     * @param array  $properties HTML tag proberties
     */
    public function __construct($src, $properties = null)
    {
        if (is_null($properties)) {
            $properties = array('src' => $src);
        } else {
            $properties['src'] = $src;
        }
        $this->setTagProperties($properties);
        parent::__construct();
    }

}

/**
 * Html element pro tlačítko
 */
class EaseHtmlButtonTag extends EaseHtmlPairTag
{

    /**
     * Html element pro tlačítko
     *
     * @param string $content         obsah tlačítka
     * @param array  $TagProperites vlastnosti tagu
     */
    function __construct($content, $tagProperties = null)
    {
        parent::__construct('button', $tagProperties, $content);
    }

}

/**
 * Html element pro adresu
 */
class EaseHtmlAddressTag extends EaseHtmlPairTag
{

    /**
     * Html element pro adresu
     *
     * @param string $content       text adresy
     * @param array  $TagProperites vlastnosti tagu
     */
    function __construct($content, $tagProperties = null)
    {
        parent::__construct('address', $tagProperties, $content);
    }

}
