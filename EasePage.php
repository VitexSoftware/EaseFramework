<?php

/**
 * Objekty pro vykreslení stránky
 *
 * @package    EaseFrameWork
 * @subpackage EaseHtml
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 */
require_once 'EaseBase.php';

/**
 * Základní třída, jenž může obsahovat vykreslující se vložené položky
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseContainer extends EaseBrick
{

    /**
     * Pole objektů a fragmentů k vykreslení
     * @var array
     */
    public $pageParts = array();

    /**
     * Byla jiz stranka vykreslena
     * @var bool
     */
    public $DrawStatus = false;

    /**
     * Znaková sada stránky
     * @var string
     */
    public $CharSet = 'utf-8';

    /**
     * Prošel už objekt finalizací ?
     * @var boolean
     */
    private $finalized = false;

    /**
     * Které objekty převzít od přebírajícího objektu
     * @var array
     */
    public $RaiseItems = array();

    /**
     * Kontejner, který může obsahovat vložené objekty, které se vykreslí
     *
     * @param mixed $InitialContent hodnota nebo EaseObjekt s metodou draw()
     */
    public function __construct($InitialContent = null)
    {
        parent::__construct();
        if ($InitialContent) {
            $this->addItem($InitialContent);
        }
    }

    /**
     * Projde $this->RaiseItems (metoda_potomka=>proměnná_rodiče) a pokud v
     * objektu najde metodu potomka, zavolá jí s parametrem
     * $this->proměnná_rodiče
     *
     * @param object $childObject  vkládaný objekt
     * @param array  $itemsToRaise pole položek k "protlačení"
     */
    public function raise(& $childObject, $itemsToRaise = null)
    {
        if (!$itemsToRaise) {
            $itemsToRaise = $childObject->RaiseItems;
        }

        foreach ($itemsToRaise as $method => $property) {
            if (method_exists($childObject, $method)) {
                if (isset($this->$property)) {
                    $childObject->$method($this->$property);
                }
            } else {
                if (isset($this->$property)) {
                    $childObject->$property = & $this->$property;
                }
            }
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
    function &addItem($pageItem,$pageItemName = null)
    {
        $itemPointer = null;
        if (is_object($pageItem)) {
            if (method_exists($pageItem, 'draw')) {
                $duplicity = 1;
                if (is_null($pageItemName)) {
                    $pageItemName = $pageItem->getObjectName();
                }

                while (isset($this->pageParts[$pageItemName])) {
                    $pageItemName = $pageItemName . $duplicity++;
                }

                $this->pageParts[$pageItemName] = $pageItem;
                $this->pageParts[$pageItemName]->parentObject = & $this;

                if (isset($this->pageParts[$pageItemName]->RaiseItems) && is_array($this->pageParts[$pageItemName]->RaiseItems) && count($this->pageParts[$pageItemName]->RaiseItems)) {
                    $this->raise($this->pageParts[$pageItemName]);
                }
                if (method_exists($this->pageParts[$pageItemName], 'AfterAdd')) {
                    $this->pageParts[$pageItemName]->afterAdd();
                }
                $this->lastItem = & $this->pageParts[$pageItemName];
                $itemPointer = & $this->pageParts[$pageItemName];
            } else {
                $this->error('Page Item object without draw() method', $pageItem);
            }
        } else {
            if (is_array($pageItem)) {
                $AddedItemPointer = $this->addItems($pageItem);
                $itemPointer = & $AddedItemPointer;
            } else {
                if (!is_null($pageItem)) {
                    $this->pageParts[] = $pageItem;
                    $EndPointer = end($this->pageParts);
                    $itemPointer = &$EndPointer;
                }
            }
        }
        EaseShared::instanced()->registerItem($itemPointer);

        return $itemPointer;
    }

    /**
     * Umožní již vloženému objektu se odstranit ze stromu k vykreslení
     */
    public function suicide()
    {
        if (isset($this->parentObject) && isset($this->parentObject->pageParts[$this->getObjectName()]) ) {
            unset($this->parentObject->pageParts[$this->getObjectName()]);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Vrací počet vložených položek
     *
     * @param EaseContainer $Object hodnota nebo EaseObjekt s polem ->pageParts
     *
     * @return int | null
     */
    public function getItemsCount($Object = null)
    {
        if (is_null($Object)) {
            return count($this->pageParts);
        }
        if (is_object($Object) && isset($Object->pageParts)) {
            return count($Object->pageParts);
        }

        return null;
    }

    /**
     * Vloží další element za stávající
     *
     * @param mixed $PageItem hodnota nebo EaseObjekt s metodou draw()
     *
     * @return pointer Odkaz na vložený objekt
     */
    function &addNextTo($PageItem)
    {
        $ItemPointer = null;
        $ItemPointer = $this->parentObject->addItem($PageItem);

        return $ItemPointer;
    }

    /**
     * Vrací odkaz na poslední vloženou položku
     *
     * @return EaseBrick|mixed
     */
    function & lastItem()
    {
        $LastPart = end($this->pageParts);

        return $LastPart;
    }

    /**
     * Přidá položku do poslední vložené položky
     *
     * @param object $PageItem hodnota nebo EaseObjekt s metodou draw()
     *
     * @return bool success
     */
    function &addToLastItem($PageItem)
    {
        if (!method_exists($this->lastItem, 'addItem')) {
            return false;
        }

        return $this->lastItem->addItem($PageItem);
    }

    /**
     * Vrací první vloženou položku
     *
     * @param EaseContainer|mixed $PageItem kontext
     *
     * @return null
     */
    function &getFirstPart($PageItem = null)
    {
        if (!$PageItem) {
            $PageItem = & $this;
        }
        if (isset($PageItem->pageParts[0])) {
            $FirstPart = reset($PageItem->pageParts);
        } else {
            $FirstPart = null;
        }

        return $FirstPart;
    }

    /**
     * Vloží pole elementů
     *
     * @param array $ItemsArray pole hodnot nebo EaseObjektů s metodou draw()
     */
    public function addItems($ItemsArray)
    {
        $ItemsAdded = array();
        foreach ($ItemsArray as $Item) {
            $ItemsAdded[] = $this->addItem($Item);
        }

        return $ItemsAdded;
    }

    /**
     * Vyprázní obsah objektu
     */
    public function emptyContents()
    {
        $this->pageParts = null;
    }

    /**
     * Metoda volaná až po přidání elementu metodou addItem()
     */
//    function AfterAdd() {
//    }

    /**
     * Převezme JavaScripty
     *
     * @param EasePage|array $Scripts pole skriptiptů nebo EaseObjekt s
     *                       vloženými skripty v poli ->JavaScripts
     */
    public function takeJavascripts(& $Scripts)
    {
        if (is_object($Scripts)) {
            $ScriptsToProcess = $Scripts->JavaScripts;
        } else {
            $ScriptsToProcess = $Scripts;
        }
        if (count($ScriptsToProcess)) {
            foreach ($ScriptsToProcess as $ScriptID => $Script) {
                if ($Script[0] == '#') {
                    $this->IncludeJavaScript(substr($Script, 1), $ScriptID);
                } else {
                    $this->addJavaScript(substr($Script, 1), $ScriptID);
                }
            }
        }
    }

    /**
     * Převezme kaskádove styly
     *
     * @param EasePage|array $Styles pole definic stylů nebo objekt s nimi
     */
    public function takeCascadeStyles($Styles)
    {
        if (is_object($Styles)) {
            $StylesToProcess = & $Styles->webPage->head->CascadeStyles;
        } else {
            $StylesToProcess = & $Styles;
        }
        if (count($StylesToProcess)) {
            foreach ($StylesToProcess as $Style) {
                $this->AddCss($Style);
            }
        }
    }

    /**
     * Projde rekurzivně všechny vložené objekty a zavolá jeich draw()
     */
    public function drawAllContents()
    {
        if (count($this->pageParts))
            foreach ($this->pageParts as $part) {
                if (is_object($part) && method_exists($part, 'draw')) {
                    $part->draw();
                } else {
                    echo $part;
                }
            }
        $this->DrawStatus = true;
    }

    /**
     * Vrací rendrovaný obsah objektů
     *
     * @return string
     */
    public function getRendered()
    {
        $RetVal = '';
        ob_start();
        $this->draw();
        $RetVal .= ob_get_contents();
        ob_clean();

        return $RetVal;
    }

    /**
     * Zobrazí schéma hierarchie vložených objektů
     *
     * @param int $Level aktuální uroven zanoření
     */
    public function showContents($Level = 0)
    {
        foreach ($this->pageParts as $partName => $PartContents) {
            if (is_object($PartContents) && method_exists($PartContents, 'ShowContents')) {
                $PartContents->showContents($Level + 1);
            } else {
                echo str_repeat('&nbsp;.&nbsp;', $Level) . $partName . '<br>';
            }
        }
    }

    /**
     * Vykresli se, pokud již tak nebylo učiněno
     */
    public function drawIfNotDrawn()
    {
        if (!$this->DrawStatus) {
            $this->draw();
        }
    }

    /**
     * Vrací stav návěští finalizace části
     *
     * @return boolean
     */
    public function isFinalized()
    {
        return $this->finalized;
    }

    /**
     * Nastaví návěstí finalizace části
     *
     * @param boolean $Flag příznak finalizace
     */
    public function setFinalized($Flag = true)
    {
        $this->finalized = $Flag;
    }

    /**
     * Naplní vložené objekty daty
     *
     * @param type $data asociativní pole dat
     */
    public function fillUp($data = null)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        self::fillMeUp($data, $this);
    }

    /**
     * Projde všechny vložené objekty a pokud se jejich jména shodují s klíči
     * dat, nastaví se jim hodnota.
     *
     * @param array               $data asociativní pole dat
     * @param EaseContainer|mixed $Form formulář k naplnění
     */
    public static function fillMeUp(&$data, &$Form)
    {
        if (isset($Form->pageParts) && is_array($Form->pageParts) && count($Form->pageParts)) {
            foreach ($Form->pageParts as $partName => $Part) {
                if (isset($Part->pageParts) && is_array($Part->pageParts) && count($Part->pageParts)) {
                    self::fillMeUp($data, $Part);
                }
                if (is_object($Part)) {
                    if (method_exists($Part, 'setValue') && method_exists($Part, 'getTagName')) {
                        $TagName = $Part->getTagName();
                        if (isset($data[$TagName])) {
                            $Part->setValue($data[$TagName], true);
                        }
                    }
                    if (method_exists($Part, 'setValues')) {
                        $Part->setValues($data);
                    }
                }
            }
        }
    }

    /**
     * Je element prázdný ?
     *
     * @return bool prázdnost
     */
    public function isEmpty()
    {
        return !count($this->pageParts);
    }

    /**
     * Vykreslí objekt z jeho položek
     */
    public function draw()
    {
        foreach ($this->pageParts as $part) {
            if (is_object($part)) {
                if (method_exists($part, 'drawIfNotDrawn')) {
                    $part->drawIfNotDrawn();
                } else {
                    $part->draw();
                }
            } else {
                echo $part;
            }
        }
    }

}

/**
 * Objekt určený k "pojmutí" obsahu - sám nemá žádnou viditelnou část
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EasePage extends EaseContainer
{

    /**
     * Saves obejct instace (singleton...)
     */
    private static $_instance = null;

    /**
     * Odkaz na základní objekt stránky
     * @var EaseWebPage
     */
    public $webPage = null;

    /**
     * Které objekty převzít od přebírajícího objektu
     * @var array
     */
    public $RaiseItems = array('SetUpUser' => 'User', 'webPage', 'OutputFormat');

    /**
     * Odkaz na naposledy přidaný element
     * @var object
     */
    public $lastItem = null;

    /**
     * Seznam názvů proměnných které se mají stabilně udržovat
     * @var array
     */
    public $RequestValuesToKeep = null;

    /**
     * Specifikuje preferovaný účel zobrazení například mail
     * @var string
     */
    public $OutputFormat = null;

    /**
     * Měna např: 'Kč'
     * @var string
     */
    public static $Currency = 'Kč';

    /**
     * Objekt vykreslující stránku
     *
     * @param EaseUser|EaseAnonym $UserObject objekt uživatele
     */
    public function __construct(& $UserObject = null)
    {
        parent::__construct();
        if (is_object($UserObject)) {
            $this->setUpUser($UserObject);
        } else {
            $this->setUpUser( EaseShared::user() );
        }
    }

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako konstruktor)
     * se bude v ramci behu programu pouzivat pouze jedna jeho instance (ta prvni).
     *
     * @param EaseUser $User objekt uživatele k přiřazení
     *
     * @link http://docs.php.net/en/language.oop5.patterns.html Dokumentace a priklad
     * @return EaseWebPage
     */
    public static function singleton($User = null)
    {
        if (!isset(self::$_instance)) {
            $Class = __CLASS__;
            self::$_instance = new $Class($User);
        }

        return self::$_instance;
    }

    /**
     * Přiřadí objekt stránky do webPage
     *
     * @param object|EasePage|EaseContainer $EaseObject objekt do kterého
     *                                      přiřazujeme WebStránku
     */
    public static function assignWebPage(&$EaseObject)
    {
        if (isset($EaseObject->easeShared->webPage)) {
            $EaseObject->webPage = &$EaseObject->easeShared->webPage;
        } else {
            if (is_subclass_of($EaseObject, 'EasePage')) {
                $EaseObject->webPage = &$EaseObject;
            } else {
                $EaseObject->webPage = &EaseShared::webPage();
            }
        }
    }

    /**
     * Vloží javascript do stránky
     *
     * @param string  $JavaScript      JS code
     * @param string  $Position        končná pozice: '+','-','0','--',...
     * @param boolean $inDocumentReady vložit do DocumentReady bloku ?
     *
     * @return int
     */
    public function addJavaScript($JavaScript, $Position = null, $inDocumentReady = false)
    {
        self::assignWebPage($this);

        return $this->webPage->addJavaScript($JavaScript, $Position, $inDocumentReady);
    }

    /**
     * Includuje Javascript do stránky
     *
     * @param string  $JavaScriptFile soubor s javascriptem
     * @param string  $Position       končná pozice: '+','-','0','--',...
     * @param boolean $FWPrefix       Přidat prefix frameworku (obvykle /Ease/)?
     *
     * @return string
     */
    public function includeJavaScript($JavaScriptFile, $Position = null, $FWPrefix = false)
    {
        self::assignWebPage($this);

        return $this->webPage->includeJavaScript($JavaScriptFile, $Position, $FWPrefix);
    }

    /**
     * Add another CSS definition to stack
     *
     * @param string $Css css definice
     *
     * @return boolean
     */
    public function addCSS($Css)
    {
        self::assignWebPage($this);

        return $this->webPage->addCSS($Css);
    }

    /**
     * Include an CSS file call into page
     *
     * @param string  $CssFile  cesta k souboru vkládanému do stránky
     * @param boolean $FWPrefix přidat prefix frameworku (obvykle /Ease/) ?
     * @param string  $media    médium screen|print|braile apod ...
     *
     * @return int
     */
    public function includeCss($CssFile, $FWPrefix = false, $media = 'screen')
    {
        self::assignWebPage($this);

        return $this->webPage->includeCss($CssFile, $FWPrefix, $media);
    }

    /**
     * Provede http přesměrování
     *
     * @param string $Url adresa přesměrování
     */
    public static function redirect($Url)
    {
        header('Location: ' . $Url);
    }

    /**
     * Vrací požadovanou adresu
     *
     * @return string
     */
    public static function getUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Returns the current URL. This is instead of PHP_SELF which is unsafe
     *
     * @param bool $dropqs whether to drop the querystring or not. Default true
     *
     * @return string the current URL
     */
    public static function phpSelf($dropqs = true)
    {
        $url = sprintf('%s://%s%s', empty($_SERVER['HTTPS']) ?
                        (@$_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http') : 'http', $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']
        );

        $parts = parse_url($url);

        $port = $_SERVER['SERVER_PORT'];
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $path = @$parts['path'];
        $qs = @$parts['query'];

        $port or $port = ($scheme == 'https') ? '443' : '80';

        if (($scheme == 'https' && $port != '443')
                || ($scheme == 'http' && $port != '80')
        ) {
            $host = "$host:$port";
        }
        $url = "$scheme://$host$path";
        if (!$dropqs)
            return "{$url}?{$qs}";
        else
            return $url;
    }

    /**
     * Nepřihlášeného uživatele přesměruje na přihlašovací stránku
     *
     * @param string $LoginPage adresa přihlašovací stránky
     */
    public function onlyForLogged($LoginPage = 'login.php')
    {
        if (!EaseShared::user()->isLogged()) {
            EaseShared::user()->addStatusMessage(_('Nejprve se prosím přihlašte'), 'warning');
            $this->redirect($LoginPage);
            exit;
        }
    }

    /**
     * Vrací pole $_REQUEST
     *
     * @return array
     */
    public function getRequestValues()
    {
        global $_REQUEST;
        $RequestValuesToKeep = array();
        if (isset($this->webPage->RequestValuesToKeep) && is_array($this->webPage->RequestValuesToKeep) && count($this->webPage->RequestValuesToKeep)) {
            foreach ($this->webPage->RequestValuesToKeep as $KeyName => $KeyValue) {
                if ($KeyValue != true) {
                    $RequestValuesToKeep[$KeyName] = $KeyValue;
                }
            }
        }

        return array_merge($RequestValuesToKeep, $_REQUEST);
    }

    /**
     * Is page called by Form Post ?
     *
     * @return boolean
     */
    public static function isPosted()
    {
        if (isset($_POST) && count($_POST)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Ošetří proměnou podle jejího očekávaného typu
     *
     * @param mixed  $value      hodnota
     * @param string $SanitizeAs typ hodnoty int|string|float|null
     *
     * @return mixed
     */
    public static function sanitizeAsType($value, $SanitizeAs)
    {
        switch ($SanitizeAs) {
            case 'string':
                return (string) $value;
                break;
            case 'int':
                return (int) $value;
                break;
            case 'float':
                return (float) $value;
                break;
            case 'bool':
            case 'boolean':
                if (($value == 'true') || ($value == 1)) {
                    return true;
                }
                break;
                if (($value == 'false') || ($value == 0)) {
                    return fals;
                }
                break;

                return null;
            case 'null':
            case 'null':
                if (strtoupper($value) == 'null') {
                    return null;
                }
            default:
                return $value;
                break;
        }
    }

    /**
     * Vrací hodnotu klíče prametru volání stránky
     *
     * @param string $Field      klíč POST nebo GET
     * @param string $SanitizeAs ošetřit vrácenou hodnotu jako float|int|string
     *
     * @return mixed
     */
    public function getRequestValue($Field, $SanitizeAs = null)
    {
        global $_REQUEST;
        $this->setupWebPage();
        if (isset($_REQUEST[$Field])) {
            if (isset($this->webPage->RequestValuesToKeep[$Field])) {
                $this->webPage->RequestValuesToKeep[$Field] = $_REQUEST[$Field];
            }
            if ($SanitizeAs) {
                return EasePage::sanitizeAsType($_REQUEST[$Field], $SanitizeAs);
            } else {
                return $_REQUEST[$Field];
            }
        } else {
            if (isset($this->RequestValuesToKeep[$Field])) {
                if ($this->RequestValuesToKeep[$Field] != true) {
                    return $this->RequestValuesToKeep[$Field];
                }
            }

            return null;
        }
    }

    /**
     * Vrací hodnotu klíče pramatru volání stránky
     *
     * @param string $Field      klíč GET
     * @param string $SanitizeAs ošetřit vrácenou hodnotu jako float|int|string
     *
     * @return string
     */
    public static function getGetValue($Field, $SanitizeAs = null)
    {
        if (isset($_GET[$Field])) {
            if ($SanitizeAs) {
                return EasePage::sanitizeAsType($_GET[$Field], $SanitizeAs);
            } else {
                return $_GET[$Field];
            }
        } else {
            return null;
        }
    }

    /**
     * Vrací hodnotu klíče pramatru volání stránky
     *
     * @param string $Field      klíč POST
     * @param string $SanitizeAs ošetřit vrácenou hodnotu jako float|int|string
     *
     * @return string
     */
    public static function getPostValue($Field, $SanitizeAs = null)
    {
        if (isset($_POST[$Field])) {
            if ($SanitizeAs) {
                return EasePage::sanitizeAsType($_POST[$Field], $SanitizeAs);
            } else {
                return $_POST[$Field];
            }
        } else {
            return null;
        }
    }

    /**
     * Byla stránka zobrazena po odeslání formuláře metodou POST ?
     *
     * @category requestValue
     * @return boolean
     */
    public static function isFormPosted()
    {
        return (isset($_POST) && count($_POST));
    }

    /**
     * Začne uchovávat hodnotu proměnné
     *
     * @category requestValue
     *
     * @param string $VarName  název klíče
     * @param mixed  $VarValue hodnota klíče
     */
    public function keepRequestValue($VarName, $VarValue = true)
    {
        EaseShared::webPage()->RequestValuesToKeep[$VarName] = $VarValue;
    }

    /**
     * Začne uchovávat hodnotu proměnných vyjmenovaných v poli
     *
     * @category requestValue
     *
     * @param array $VarNames asociativní pole hodnot
     */
    public function keepRequestValues($VarNames)
    {
        if (is_array($VarNames)) {
            foreach ($VarNames as $VarName => $VarValue) {
                if (is_numeric($VarName)) {
                    $VarName = $VarValue;
                    $VarValue = $this->getRequestValue($VarName);
                    if ($VarValue) {
                        $this->keepRequestValue($VarName, $VarValue);
                    } else {
                        $this->keepRequestValue($VarName, true);
                    }
                } else {
                    $this->keepRequestValue($VarName, $VarValue);
                }

                /*
                  {
                  if ($VarName == $VarValue) {
                  if (!isset($this->webPage->RequestValuesToKeep[$VarName])) {
                  $this->KeepRequestValue($VarValue, true);
                  }
                  } else {
                  $this->KeepRequestValue($VarName, $VarValue);
                  }
                  }
                 */
            }
        }
    }

    /**
     * Zruší zachovávání hodnoty proměnné
     *
     * @category requestValue
     *
     * @param string $VarName jméno proměnné
     */
    public function unKeepRequestValue($VarName)
    {
        unset(EaseShared::webPage()->RequestValuesToKeep[$VarName]);
    }

    /**
     * Zruší zachovávání hodnot proměnných
     *
     * @category requestValue
     */
    public function unKeepRequestValues()
    {
        EaseShared::webPage()->RequestValuesToKeep = array();
    }

    /**
     * Vrací fragment udrživaných hodnot pro link
     *
     * @category requestValue
     *
     * @return string
     */
    public function getLinkParametersToKeep()
    {
        $RequestValuesToKeep = EaseShared::webPage()->RequestValuesToKeep;

        if (is_null($RequestValuesToKeep) || !is_array($RequestValuesToKeep) || !count($RequestValuesToKeep)) {
            return '';
        }
        $ArgsToKeep = array();
        foreach ($RequestValuesToKeep as $name => $value) {
            if (is_string($value) && strlen($value)) {
                $ArgsToKeep[$name] = $name . '=' . $value;
            }
        }

        return implode('&amp;', $ArgsToKeep);
    }

    /**
     * Zapamatuje si odkaz na základní stránku webu
     *
     * @param EaseWebPage|true $webPage Objekt stránky, true - force assign
     */
    public function setupWebPage(& $webPage = null)
    {
        if (is_null($webPage)) {
            $webPage = & $this;
        }

        if (!isset($this->webPage) || !is_object($this->webPage)) {
            $this->webPage = $webPage;
        }
    }

    /**
     * Nastaví formát výstupu
     *
     * @param string $OutputFormat výstupní formát, např Mail nebo Print
     */
    public function setOutputFormat($OutputFormat)
    {
        $this->OutputFormat = $OutputFormat;
        foreach ($this->pageParts as $Part) {
            $this->raise($Part, array('OutputFormat'));
        }
    }

    /**
     * Vrací formát výstupu
     */
    public function getOutputFormat()
    {
        return $this->OutputFormat;
    }

    /**
     * Vrací styl logování
     *
     * @param string $logType typ logu warning|info|success|error|notice|*
     *
     * @return string
     */
    public function getLogStyle($logType = 'notice')
    {
        if (key_exists($logType, $this->LogStyles)) {
            return $this->LogStyles[$logType];
        } else {
            return '';
        }
    }

    /**
     * Převezme hlášky z pole nebo objektu
     *
     * @param mixed $MsgSource zdroj zpráv - pole nebo EaseObjekt
     * @param array $DenyQues  neprevezme tyto typy
     *
     * @return int počet převzatých hlášek
     */
    public function takeStatusMessages($MsgSource, $DenyQues = null)
    {
        if (is_array($MsgSource) && count($MsgSource)) {
            $AllMessages = array();
            foreach ($MsgSource as $Quee => $Messages) {
                if (is_array($DenyQues) && in_array($Quee, $DenyQues)) {
                    continue;
                }
                foreach ($Messages as $MesgID => $Message) {
                    $AllMessages[$MesgID][$Quee] = $Message;
                }
            }
            ksort($AllMessages);
            foreach ($AllMessages as $Message) {
                $Quee = key($Message);
                $this->addStatusMessage(reset($Message), $Quee, false, false);
            }

            return count($MsgSource);
        }
        if (is_object($MsgSource)) {
            if (isset($MsgSource->statusMessages) && count($MsgSource->statusMessages)) {
                $MsgTaken = count($MsgSource->statusMessages);
                $this->addStatusMessages($MsgSource->getStatusMessages(true));

                return $MsgTaken;
            } else {
                if (isset($MsgSource->OPage) && isset($MsgSource->OPage->statusMessages) && count($MsgSource->OPage->statusMessages)) {
                    $MsgTaken = count($MsgSource->OPage->statusMessages);
                    $this->statusMessages = array_merge($this->statusMessages, $MsgSource->OPage->statusMessages);
                    $MsgSource->OPage->statusMessages = array();

                    return $MsgTaken;
                }
            }
        }

        return 0;
    }

}
