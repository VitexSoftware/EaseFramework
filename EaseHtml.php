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
    public $TagType = null;

    /**
     * Pole vlastností tagu
     * @var array
     */
    public $TagProperties = null;

    /**
     * pole ze kterého se rendruje obsah STYLE tagu
     * @var array
     */
    public $CssProperties = null;

    /**
     * Nelogovat události HTML objektů
     * @var string
     */
    public $LogType = 'none';

    /**
     * Koncové lomítko pro xhtml
     * @var string
     */
    public $Trail = ' /';

    /**
     * Má si objekt automaticky plnit vlastnost name ?
     */
    public $SetName = false;

    /**
     * Objekt pro vykreslení obecného nepárového html tagu
     * 
     * @param string       $TagType       typ tagu
     * @param array|string $TagProperties parametry tagu
     * @param mixed        $Content       vkládaný obsah
     */
    function __construct($TagType = null, $TagProperties = null, $Content = null)
    {
        if (is_null($TagType)) {
            $TagType = $this->TagType;
        } else {
            $this->setTagType($TagType);
        }
        parent::__construct();
        if ($TagProperties) {
            $this->setTagProperties($TagProperties);
        }
        if ($Content) {
            $this->addItem($Content);
        }
    }

    /**
     * Nastaví jméno objektu
     * 
     * @param string $objectName jméno objektu
     * 
     * @return string New object name 
     */
    function setObjectName($objectName = null)
    {
        if ($objectName) {
            return parent::setObjectName($objectName);
        }
        if ($this->tagName) {
            return parent::setObjectName(get_class($this) . '@' . $this->tagName);
        } else {
            if ($this->TagType) {
                return parent::setObjectName(get_class($this) . '@' . $this->TagType);
            } else {
                return parent::setObjectName();
            }
        }
    }

    /**
     * Nastaví jméno tagu
     * 
     * @param string $TagName jméno tagu do vlastnosti NAME
     */
    function setTagName($TagName)
    {
        $this->tagName = $TagName;
        if ($this->SetName) {
            $this->TagProperties['name'] = $TagName;
        }
        $this->setObjectName();
    }

    /**
     * Returns name of tag
     * 
     * @return string 
     */
    function getTagName()
    {
        if ($this->SetName) {
            if (isset($this->TagProperties['name'])) {
                return $this->TagProperties['name'];
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
     * @param string $TagType typ tagu - např. img
     */
    function setTagType($TagType)
    {
        $this->TagType = $TagType;
    }

    /**
     * Nastaví classu tagu
     * 
     * @param string $ClassName jméno třídy
     */
    function setTagClass($ClassName)
    {
        $this->setTagProperties(array('class' => $ClassName));
    }
    
    /**
     * Vrací classu tagu
     */
    function getTagClass()
    {
        $this->getTagProperty('class');
    }

    /**
     * Nastaví tagu zadane id, nebo vygenerované náhodné
     * 
     * @param string $TagID #ID html tagu pro JavaScript a Css
     * 
     * @return string nastavené ID
     */
    function setTagID($TagID = null)
    {
        if (is_null($TagID)) {
            $this->setTagProperties(array('id' => EaseBrick::randomString()));
        } else {
            $this->setTagProperties(array('id' => $TagID));
        }
        return $this->getTagID();
    }

    /**
     * Vrací ID tagu
     * 
     * @return string
     */
    function getTagID()
    {
        if (isset($this->TagProperties['id'])) {
            return $this->TagProperties['id'];
        } else {
            return null;
        }
    }

    /**
     * Returns property tag value 
     * 
     * @param string $PropertyName název vlastnosti tagu. např. "src" u obrázku
     * 
     * @return string current tag property value 
     */
    public function getTagProperty($PropertyName)
    {
        if (isset($this->TagProperties[$PropertyName])) {
            return $this->TagProperties[$PropertyName];
        }
        return null;
    }

    /**
     * Nastaví paramatry tagu
     * 
     * @param mixed $TagProperties asociativní pole parametrů tagu 
     */
    function setTagProperties($TagProperties)
    {
        if (is_array($TagProperties)) {
            if (is_array($this->TagProperties)) {
                $this->TagProperties = array_merge($this->TagProperties, $TagProperties);
            } else {
                $this->TagProperties = $TagProperties;
            }
            if (isset($TagProperties['name'])) {
                $this->setTagName($TagProperties['name']);
            }
        } else {
            $propBuff = $TagProperties;
            //if (substr($propBuff, 0, 1) != ' ') $propBuff = ' ' . $TagProperties;
            $this->TagProperties = ' ' . $propBuff;
        }
    }

    /**
     * Vrátí parametry tagu jako řetězec
     * 
     * @param mixed $tagProperties asociativní pole parametrú nebo řetězec
     * 
     * @return string
     */
    function tagPropertiesToString($tagProperties = null)
    {
        if (!$tagProperties) {
            $tagProperties = $this->TagProperties;
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
            return $this->TagProperties;
        }
    }

    /**
     * Nastaví paramatry Css
     * 
     * @param array|string $CssProperties asociativní pole, nebo CSS definice
     */
    function setTagCss($CssProperties)
    {
        if (is_array($CssProperties)) {
            if (is_array($this->CssProperties)) {
                $this->CssProperties = array_merge($this->CssProperties, $CssProperties);
            } else {
                $this->CssProperties = $CssProperties;
            }
        } else {
            $propBuff = $CssProperties;
            //if (substr($propBuff, 0, 1) != ' ') $propBuff = ' ' . $CssProperties;
            $this->CssProperties = ' ' . $propBuff;
        }
        $this->setTagProperties(array('style' => $this->cssPropertiesToString()));
    }

    /**
     * Vrátí parametry Cssu jako řetězec
     * 
     * @param array|string $CssProperties pole vlastností nebo CSS definice
     * 
     * @return string
     */
    function cssPropertiesToString($CssProperties = null)
    {
        if (!$CssProperties) {
            $CssProperties = $this->CssProperties;
        }
        if (is_array($CssProperties)) {
            $CssPropertiesString = ' ';
            foreach ($CssProperties as $CssPropertyName => $CssPropertyValue) {
                $CssPropertiesString .= $CssPropertyName . ':' . $CssPropertyValue . ';';
            }
            return $CssPropertiesString;
        } else {
            return $this->CssProperties;
        }
    }

    /**
     * Vykreslí tag
     */
    function draw()
    {
        echo "\n<" . $this->TagType;
        echo $this->tagPropertiesToString();
        echo $this->Trail;
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
    public $Trail = '';

    /**
     * Render tag and its contents
     */
    function draw()
    {
        $this->tagBegin();
        $this->drawAllContents();
        $this->tagEnclousure();
    }

    /**
     * Zobrazí počátek párového tagu
     */
    function tagBegin()
    {
        parent::draw();
    }

    /**
     * Zobrazí konec párového tagu
     */
    function tagEnclousure()
    {
        echo '</' . $this->TagType . ">\n";
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
     * @param string $Image         url obrázku
     * @param string $Hint          hint při nájezu myší
     * @param int    $Width         šířka v pixelech
     * @param int    $Height        výška v pixelech
     * @param array  $TagProperties ostatni nastaveni tagu
     */
    function __construct($Image, $Hint = null, $Width = null, $Height = null, $TagProperties = null)
    {
        if (is_null($TagProperties)) {
            $TagProperties = array();
        }
        $TagProperties['src'] = $Image;
        if (isset($Hint)) {
            $TagProperties['title'] = $Hint;
        }
        if (isset($Width)) {
            $TagProperties['width'] = $Width;
        }
        if (isset($Height)) {
            $TagProperties['height'] = $Height;
        }
        parent::__construct('img', $TagProperties);
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
     * @param mixed $Content    vkládaný obsah
     * @param array $Properties parametry tagu
     */
    function __construct($Content = null, $Properties = null)
    {
        parent::__construct('p', $Properties, $Content);
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
     * @param mixed $Content    vkládaný obsah
     * @param array $Properties parametry tagu
     */
    function __construct($Content = null, $Properties = null)
    {
        parent::__construct('td', $Properties, $Content);
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
     * @param mixed $Content    vkládaný obsah
     * @param array $Properties parametry tagu
     */
    function __construct($Content = null, $Properties = null)
    {
        parent::__construct('th', $Properties, $Content);
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
     * @param mixed $Content    vkládaný obsah
     * @param array $Properties parametry tagu
     */
    function __construct($Content = null, $Properties = null)
    {
        parent::__construct('tr', $Properties, $Content);
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
     * Html Tabulka
     * 
     * @param mixed $Content    vkládaný obsah
     * @param array $Properties parametry tagu
     */
    function __construct($Content = null, $Properties = null)
    {
        parent::__construct('table', $Properties, $Content);
    }

    /**
     * Vloží do tabulky obsah pole jako buňky
     * 
     * @param array $Columns    pole obsahů buňek
     * @param array $Properties pole vlastností dané všem buňkám
     * 
     * @return EaseHtmlTrTag odkaz na řádku tabulky
     */
    function & addRowColumns($Columns = null, $Properties = null)
    {
        $TableRow = $this->addItem(new EaseHtmlTrTag());
        if (is_array($Columns)) {
            foreach ($Columns as $Column) {
                $TableRow->addItem(new EaseHtmlTdTag($Column, $Properties));
            }
        }
        return $TableRow;
    }

    /**
     * Vloží do tabulky obsah pole jako buňky
     * 
     * @param array $Columns    pole obsahů buňek
     * @param array $Properties pole vlastností dané všem buňkám
     * 
     * @return EaseHtmlTrTag odkaz na řádku tabulky
     */
    function & addRowHeaderColumns($Columns = null, $Properties = null)
    {
        $TableRow = $this->addItem(new EaseHtmlTrTag());
        if (is_array($Columns)) {
            foreach ($Columns as $Column) {
                $TableRow->addItem(new EaseHtmlThTag($Column, $Properties));
            }
        }
        return $TableRow;
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
     * $this->EaseShared->WebPage->Body
     * 
     * @param string $TagID   id tagu
     * @param mixed  $Content vkládané prvky
     */
    function __construct($TagID = null, $Content = null)
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
    function setObjectName($ObjectName = null)
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
    function __construct($Content = null)
    {
        parent::__construct('html', array('lang' => $this->LangCode, 'xmlns' => 'http://www.w3.org/1999/xhtml', 'xml:lang' => $this->LangCode), $Content);
    }

    /**
     * Nastaví jméno objektu na "html"
     * 
     * @param string $ObjectName jméno objektu
     */
    function setObjectName($ObjectName = null)
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
    static public $ContentType = 'text/html';

    /**
     * Head tag with defined meta http-equiv content type 
     * 
     * @param mixed $Contents   vkládaný obsah
     * @param array $Properties parametry tagu
     */
    function __construct($Contents = null, $Properties = null)
    {
        parent::__construct('head', $Properties, $Contents);
        $this->addItem('<meta http-equiv="Content-Type" content="' . self::$ContentType . '; charset=' . $this->CharSet . '" />');
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
    function __construct($Contents = null, $Properties = null)
    {
        parent::__construct('title', $Properties, $Contents);
    }

}

/**
 * HTML WebPage head class
 * 
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlHeadTag extends EaseHtmlPairTag
{

    /**
     * Javascripts to render in page
     * @var array 
     */
    public $JavaScripts = null;

    /**
     * Css definitions
     * @var strig 
     */
    public $CascadeStyles = null;

    /**
     * Html HEAD tag with basic contents and skin support
     * 
     * @param mixed $Content vkládaný obsah
     */
    function __construct($Content = null)
    {
        parent::__construct('head', null, $Content);
        $this->addItem('<meta http-equiv="Content-Type" content="text/html; charset=' . $this->CharSet . '" />');
    }

    /**
     * Change name directly to head
     * 
     * @param string $ObjectName jméno objektu
     */
    function setObjectName($ObjectName = null)
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
    static function jsEnclosure($JavaScript)
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
    function finalize()
    {
        $this->addItem('<title>' . $this->WebPage->PageTitle . '</title>');
    }

    /**
     * Vykreslí hlavičku HTML stránky
     */
    function draw()
    {

        if (isset($this->EaseShared->CascadeStyles) && count($this->EaseShared->CascadeStyles)) {
            $CascadeStyles = array();
            foreach ($this->EaseShared->CascadeStyles as $StyleRes => $Style) {
                if ($StyleRes == $Style) {
                    $this->addItem('<link href="' . $Style . '" rel="stylesheet" type="text/css" media="' . 'screen' . '" />'); //TODO: solve screen
                } else {
                    $CascadeStyles[] = $Style;
                }
            }
            $this->addItem('<style>' . implode("\n", $CascadeStyles) . '</style>');
        }

        if (isset($this->EaseShared->JavaScripts) && count($this->EaseShared->JavaScripts)) {
            ksort($this->EaseShared->JavaScripts, SORT_NUMERIC);

            $ODRStack = array();

            foreach ($this->EaseShared->JavaScripts as $Script) {
                $ScriptType = $Script[0];
                $ScriptBody = substr($Script, 1);
                switch ($ScriptType) {
                    case '#':
                        $this->addItem("\n".'<script src="' . $ScriptBody . '"></script>');
//                      EaseShared::webPage()->Body->addItem("\n".'<script type="text/javascript" src="' . $ScriptBody . '"></script>'); //TODO: rozchodit
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
                        self::jsEnclosure('$(document).ready(function() { ' .
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
     * @param string $Href       url odkazu
     * @param mixed  $contents   vkládaný obsah
     * @param array  $properties parametry tagu
     */
    function __construct($href, $contents = null, $properties = null)
    {
        if (!is_array($properties)){
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
    function afterAdd()
    {
        if (isset($this->WebPage->RequestValuesToKeep) && is_array($this->WebPage->RequestValuesToKeep) && count($this->WebPage->RequestValuesToKeep)) {
            foreach ($this->WebPage->RequestValuesToKeep as $KeepName => $KeepValue) {
                if ($KeepValue == true) {
                    continue;
                }
                $Keep = urlencode($KeepName) . '=' . urlencode($KeepValue);
                if (!strstr($this->TagProperties['href'], urlencode($KeepName) . '=')) {
                    if (strstr($this->TagProperties['href'], '?')) {
                        $this->TagProperties['href'] .= '&' . $Keep;
                    } else {
                        $this->TagProperties['href'] .= '?' . $Keep;
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
    function __construct($ulContents = null,$properties = null)
    {
        parent::__construct('li', $properties, $ulContents);
    }

}

/**
 * HTML sorted list
 * 
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlUlTag extends EaseHtmlPairTag
{

    /**
     * Vytvori UL container
     * 
     * @param mixed $UlContents položky seznamu
     * @param array $Properties parametry tagu
     */
    function __construct($UlContents = null, $Properties = null)
    {
        parent::__construct('ul', $Properties, $UlContents);
    }

    /**
     * Every item id added in EaseHtmlLiTag envelope
     * 
     * @param mixed  $pageItem     obsah vkládaný jako položka výčtu
     * @param string $pageItemName Pod tímto jménem je objekt vkládán do stromu
     * 
     * @return mixed 
     */
    function & addItemSmart($pageItem,$pageItemName = null)
    {
        if (is_array($pageItem)) {
            foreach ($pageItem as $item){
                $this->addItemSmart($item);
            }
            $itemAdded = & $this->LastItem;
        } else {
            if( isset($pageItem->TagType) && ($pageItem->TagType == 'li') ){
                $itemAdded = parent::addItem($pageItem);
            } else {
                $itemAdded = parent::addItem(new EaseHtmlLiTag($pageItem));
            }
        }
        return $itemAdded;
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
     * @param mixed $Content    vkládaný obsah
     * @param array $Properties parametry tagu
     */
    function __construct($Content = null, $Properties = null)
    {
        parent::__construct('strong', $Properties, $Content);
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
     * @param mixed $Content    vkládaný obsah
     * @param array $Properties parametry tagu
     */
    function __construct($Content = null, $Properties = null)
    {
        parent::__construct('h1', $Properties, $Content);
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
     * @param mixed  $Content    text nadpisu
     * @param string $Properties parametry tagu
     */
    function __construct($Content = null, $Properties = null)
    {
        parent::__construct('h2', $Properties, $Content);
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
     * @param mixed $Content    vkládaný obsah
     * @param array $Properties parametry tagu
     */
    function __construct($Content = null, $Properties = null)
    {
        parent::__construct('h3', $Properties, $Content);
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
     * @param mixed $Content    vkládaný obsah
     * @param array $Properties parametry tagu
     */
    function __construct($Content = null, $Properties = null)
    {
        parent::__construct('h4', $Properties, $Content);
    }

}

/**
 * HTML Div tag
 * 
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
    function __construct($name = null, $content = null, $properties = null)
    {
        if (!is_null($name)) {
            $this->setTagName($name);
            $this->setTagID($name);
        }
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
     * @param mixed  $content    vložené prvky
     * @param array  $properties pole parametrů
     */
    function __construct($content = null, $properties = null)
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
     * @param string $Name       jméno a ID tagu
     * @param mixed  $Content    vkládaný obsah
     * @param array  $Properties parametry tagu
     */
    function __construct($Name, $Content = null, $Properties = null)
    {
        if ($Name) {
            $this->setTagName($Name);
        }
        parent::__construct('span', $Properties, $Content);
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
     * @param string|mixed $Legend  popisek - text nebo Ease objekty
     * @param mixed        $Content prvky vkládané do rámečku
     */
    function __construct($Legend, $Content = null)
    {
        $this->setTagName($Legend);
        $this->Legend = $Legend;
        $this->LegendTag = $this->addItem(new EaseHtmlPairTag('legend', null, $this->Legend));
        if ($Content) {
            $this->addItem($Content);
        }
        parent::__construct('fieldset');
    }

    /**
     * Nastavení legendy
     * 
     * @param string $Legend popisek
     */
    function setLegend($Legend)
    {
        $this->Legend = $Legend;
    }

    /**
     * Vložení legendy
     */
    function finalize()
    {
        if ($this->Legend) {
            if (is_object(reset($this->PageParts))) {
                reset($this->PageParts)->PageParts = array($this->Legend);
            } else {
                array_unshift($this->PageParts, $this->LegendTag);
                reset($this->PageParts)->PageParts = array($this->Legend);
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
    function &addItem($cData,$pageItemName = null)
    {
        return parent::addItem("\n//<![CDATA[\n" . $cData . "\n// ]]>\n",$pageItemName);
    }

    /**
     * fragment skriptu ve stránce
     * 
     * @param string $content text scriptu
     */
    function __construct($content=  '', $properties = NULL)
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
    function __construct($content,$properties = null)
    {
        if (is_null($properties)){
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
     * @param string $Name  jméno parametru
     * @param string $Value hodnota parametru
     */
    function __construct($Name, $Value)
    {
        parent::__construct('param', array('name' => $Name, 'value' => $Value));
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
    function __construct($src, $quality, $bgcolor, $width, $height, $name, $align, $allowScriptAccess, $allowFullScreen, $type, $pluginspage)
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
 * HTML Flash embeding
 * 
 * @deprecated since version 174
 * @author Vitex <vitex@hippy.cz>
 */
class EaseFlash extends EaseHtmlPairTag
{

    /**
     * Flash params
     * @var array
     */
    public $FlashParams = array(
        'allowScriptAccess' => 'sameDomain',
        'allowFullScreen' => 'false',
        'quality' => 'high'
    );

    /**
     * Vlozi SWF objekt
     * 
     * @param string $DivID   id prvku
     * @param string $Movie   url FLASHe
     * @param int    $Width   šířka prvku
     * @param int    $Height  výška prvku
     * @param color  $Bgcolor barva pozadí
     * @param string $Align   zarovnávání
     */
    function __construct($DivID, $Movie, $Width, $Height, $Bgcolor = '#FFFFFF', $Align = 'middle')
    {
        parent::__construct('object', array(
            'classid' => 'clsid:d27cdb6e-ae6d-11cf-96b8-444553540000',
            'codebase' => 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0',
            'width' => $Width,
            'height' => $Height,
            'align' => $Align,
            'id' => $DivID
        ));
        $this->setTagName($DivID);
        $this->setParameter(array('movie' => $Movie, 'bgcolor' => $Bgcolor));
        $this->addItem(new EaseHtmlEmbedTag($Movie, $this->FlashParams['quality'], $this->FlashParams['bgcolor'], $Width, $Height, $this->getTagName(), $Align, $this->FlashParams['allowScriptAccess'], $this->FlashParams['allowFullScreen'], 'application/x-shockwave-flash', 'http://www.adobe.com/go/getflashplayer'));
    }

    /**
     * Setup flash params
     * 
     * @param array $FlashParameters parametry předávané flashi
     */
    function setParameter($FlashParameters)
    {
        if (is_array($FlashParameters)) {
            if (is_array($this->FlashParams)) {
                $this->FlashParams = array_merge($this->FlashParams, $FlashParameters);
            } else {
                $this->FlashParams = $FlashParameters;
            }
            if (isset($FlashParameters['name'])) {
                $this->setTagName($FlashParameters['name']);
            }
        } else {
            $propBuff = $FlashParameters;
            //if (substr($propBuff, 0, 1) != ' ') $propBuff = ' ' . $TagProperties;
            $this->FlashParams = ' ' . $propBuff;
        }
    }

    /**
     * Finalizing object before its draw
     */
    function finalize()
    {
        foreach ($this->FlashParams as $ParamName => $ParamValue) {
            $this->addItem(new EaseHtmlParamTag($ParamName, $ParamValue));
        }
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
     * @param array $Properties parametry tagu
     */
    function __construct($Properties = null)
    {
        parent::__construct('hr', $Properties);
    }

}

/**
 * iFrame element
 * 
 * @author Vitex <vitex@hippy.cz>
 */
class EaseHtmlIframeTag extends EaseHtmlPairTag
{

    public $TagType = 'iframe';

    /**
     * iFrame element
     * 
     * @param string $Src        content url 
     * @param array  $Properties HTML tag proberties
     */
    function __construct($Src, $Properties = null)
    {
        if (is_null($Properties)) {
            $Properties = array('src' => $Src);
        } else {
            $Properties['src'] = $Src;
        }
        $this->setTagProperties($Properties);
        parent::__construct();
    }

}

/**
 * Html element pro tlačítko
 */
class EaseHtmlButtonTag extends EaseHtmlPairTag {
    /**
     * Html element pro tlačítko
     * 
     * @param string $Label obsah tlačítka
     * @param array $TagProperites vlastnosti tagu
     */
     function __construct($Label, $TagProperties = null)
     {
         parent::__construct('button', $TagProperties, $Label);
     }
}
