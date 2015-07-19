<?php

/**
 * Párový HTML tag
 *
 * @package    EaseFrameWork
 * @subpackage EaseHtml
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 */
require_once 'EaseHtmlTag.php';

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
     * @param string $javaScript vkládaný skript
     *
     * @return string
     */
    public static function jsEnclosure($javaScript)
    {
        return '
<script>
// <![CDATA[
' . $javaScript . '
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
     * @param array  $tagProperties   vlastnosti tagu
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

/**
 * Definiční list
 */
class EaseHtmlDlTag extends EaseHtmlPairTag
{

    /**
     * Definice
     *
     * @param mixed $content
     * @param array $tagProperties vlastnosti tagu
     */
    public function __construct($content = null, $tagProperties = null)
    {
        parent::__construct('dl', $tagProperties, $content);
    }

    /**
     * Vloží novou definici
     *
     * @param string|mixed $term    Subjekt
     * @param string|mixed $value   Popis subjektu
     */
    function addDef($term, $value)
    {
        $this->addItem(new EaseHtmlDtTag($term));
        $this->addItem(new EaseHtmlDdTag($value));
    }

}

/**
 * Pojem definice
 */
class EaseHtmlDtTag extends EaseHtmlPairTag
{

    /**
     * Pojem definice
     *
     * @param string|mixed $content        název pojmu / klíčové slovo
     * @param array        $tagProperties
     */
    public function __construct($content = null, $tagProperties = null)
    {
        parent::__construct('dt', $tagProperties, $content);
    }

}

/**
 * Obsah definice
 */
class EaseHtmlDdTag extends EaseHtmlPairTag
{

    /**
     * Obsah definice
     *
     * @param string|mixed $content
     * @param array        $tagProperties
     */
    public function __construct($content = null, $tagProperties = null)
    {
        parent::__construct('dd', $tagProperties, $content);
    }

}

/**
 * Preformátovaný text
 */
class EaseHtmlPreTag extends EaseHtmlPairTag
{

    /**
     * Preformátovaný text
     *
     * @param string|mixed $content
     * @param array        $tagProperties
     */
    public function __construct($content = null, $tagProperties = null)
    {
        parent::__construct('pre', $tagProperties, $content);
    }

}

/**
 * Skript ve stránce
 */
class EaseHtmlScriptTag extends EaseHtmlPairTag
{

    /**
     * Skript
     *
     * @param string|mixed $content
     * @param array        $tagProperties
     */
    public function __construct($content = null, $tagProperties = null)
    {
        parent::__construct('script', $tagProperties, '// <![CDATA[
' . $content . '
// ]]>');
    }

}
