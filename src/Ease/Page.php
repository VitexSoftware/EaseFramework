<?php
/**
 * Simple html page class.
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2014 Vitex@hippy.cz (G)
 */

namespace Ease;

/**
 * Objekt určený k "pojmutí" obsahu - sám nemá žádnou viditelnou část.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class Page extends Container
{
    /**
     * Saves obejct instace (singleton...).
     */
    private static $_instance = null;

    /**
     * Odkaz na základní objekt stránky.
     *
     * @var EaseWebPage
     */
    public $webPage = null;

    /**
     * Které objekty převzít od přebírajícího objektu.
     *
     * @var array
     */
    public $raiseItems = ['SetUpUser' => 'User', 'webPage', 'OutputFormat'];

    /**
     * Odkaz na naposledy přidaný element.
     *
     * @var object
     */
    public $lastItem = null;

    /**
     * Seznam názvů proměnných které se mají stabilně udržovat.
     *
     * @var array
     */
    public $requestValuesToKeep = null;

    /**
     * Specifikuje preferovaný účel zobrazení například mail.
     *
     * @var string
     */
    public $outputFormat = null;

    /**
     * Is page closed for adding new contents ?
     * @var boolean
     */
    public $pageClosed = false;

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako konstruktor)
     * se bude v ramci behu programu pouzivat pouze jedna jeho instance (ta prvni).
     *
     * @param EaseUser $user objekt uživatele k přiřazení
     *
     * @link   http://docs.php.net/en/language.oop5.patterns.html Dokumentace a priklad
     *
     * @return EaseWebPage
     */
    public static function singleton($user = null)
    {
        if (!isset(self::$_instance)) {
            $class = __CLASS__;
            self::$_instance = new $class($user);
        }

        return self::$_instance;
    }

    /**
     * Přiřadí objekt stránky do webPage.
     *
     * @param object|Page|Container $easeObject objekt do kterého
     *                                          přiřazujeme WebStránku
     */
    public static function assignWebPage(&$easeObject)
    {
        if (isset($easeObject->easeShared->webPage)) {
            $easeObject->webPage = &$easeObject->easeShared->webPage;
        } else {
            if (is_subclass_of($easeObject, 'EasePage')) {
                $easeObject->webPage = &$easeObject;
            } else {
                $easeObject->webPage = &Shared::webPage();
            }
        }
    }

    /**
     * Vloží javascript do stránky.
     *
     * @param string $javaScript      JS code
     * @param string $position        končná pozice: '+','-','0','--',...
     * @param bool   $inDocumentReady vložit do DocumentReady bloku ?
     *
     * @return int
     */
    public function addJavaScript($javaScript, $position = null,
                                  $inDocumentReady = true)
    {
        self::assignWebPage($this);

        return $this->webPage->addJavaScript($javaScript, $position,
                $inDocumentReady);
    }

    /**
     * Includuje Javascript do stránky.
     *
     * @param string $javaScriptFile soubor s javascriptem
     * @param string $position       končná pozice: '+','-','0','--',...
     * @param bool   $fwPrefix       Přidat prefix frameworku (obvykle /Ease/)?
     *
     * @return string
     */
    public function includeJavaScript($javaScriptFile, $position = null,
                                      $fwPrefix = false)
    {
        self::assignWebPage($this);

        return $this->webPage->includeJavaScript($javaScriptFile, $position,
                $fwPrefix);
    }

    /**
     * Add another CSS definition to stack.
     *
     * @param string $css css definice
     *
     * @return bool
     */
    public function addCSS($css)
    {
        self::assignWebPage($this);

        return $this->webPage->addCSS($css);
    }

    /**
     * Include an CSS file call into page.
     *
     * @param string $cssFile  cesta k souboru vkládanému do stránky
     * @param bool   $fwPrefix přidat prefix frameworku (obvykle /Ease/) ?
     * @param string $media    médium screen|print|braile apod ...
     *
     * @return int
     */
    public function includeCss($cssFile, $fwPrefix = false, $media = 'screen')
    {
        self::assignWebPage($this);

        return $this->webPage->includeCss($cssFile, $fwPrefix, $media);
    }

    /**
     * Provede http přesměrování.
     *
     * @param string $url adresa přesměrování
     */
    public static function redirect($url)
    {
        $messages = Shared::instanced()->statusMessages;
        if (count($messages)) {
            $_SESSION['EaseMessages'] = $messages;
        }
        header('Location: '.$url);
        $this->pageClosed = true;
    }

    /**
     * Vrací požadovanou adresu.
     *
     * @return string
     */
    public static function getUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Returns the current URL. This is instead of PHP_SELF which is unsafe.
     *
     * @param bool $dropqs whether to drop the querystring or not. Default true
     *
     * @return string the current URL
     */
    public static function phpSelf($dropqs = true)
    {
        $url = sprintf(
            '%s://%s%s',
            empty($_SERVER['HTTPS']) ?
                ($_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http') : 'http',
            $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']
        );

        $parts = parse_url($url);

        $port = $_SERVER['SERVER_PORT'];
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        if (isset($parts['path'])) {
            $path = $parts['path'];
        } else {
            $path = null;
        }
        if (isset($parts['query'])) {
            $qs = $parts['query'];
        } else {
            $qs = null;
        }
        $port or $port = ($scheme == 'https') ? '443' : '80';

        if (($scheme == 'https' && $port != '443') || ($scheme == 'http' && $port
            != '80')
        ) {
            $host = "$host:$port";
        }
        $url = "$scheme://$host$path";
        if (!$dropqs) {
            return "{$url}?{$qs}";
        } else {
            return $url;
        }
    }

    /**
     * Nepřihlášeného uživatele přesměruje na přihlašovací stránku.
     *
     * @param string $loginPage adresa přihlašovací stránky
     */
    public function onlyForLogged($loginPage = 'login.php')
    {
        $user = Shared::user();
        if (!method_exists($user, 'isLogged') || !$user->isLogged()) {
            Shared::user()->addStatusMessage(_('Nejprve se prosím přihlašte'),
                'warning');
            $this->redirect($loginPage);
            $this->pageClosed = true;
        }
    }

    /**
     * Include next element into current page (if not closed).
     *
     * @param mixed  $pageItem     value or EaseClass with draw() method.
     * @param string $pageItemName Custom 'storing' name.
     *
     * @return mixed Pointer to included object
     */
    public function addItem($pageItem, $pageItemName = null)
    {
        $result = null;
        if ($this->pageClosed === false) {
            $result = parent::addItem($pageItem, $pageItemName);
        }
        return $result;
    }

    /**
     * Vrací pole $_REQUEST.
     *
     * @return array
     */
    public function getRequestValues()
    {
        $requestValuesToKeep = [];
        if (isset($this->webPage->requestValuesToKeep) && is_array($this->webPage->requestValuesToKeep)
            && count($this->webPage->requestValuesToKeep)) {
            foreach ($this->webPage->requestValuesToKeep as $KeyName => $keyValue) {
                if ($keyValue !== true) {
                    $requestValuesToKeep[$KeyName] = $keyValue;
                }
            }
        }

        return array_merge($requestValuesToKeep, $_REQUEST);
    }

    /**
     * Is page called by Form Post ?
     *
     * @return bool
     */
    public static function isPosted()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Ošetří proměnou podle jejího očekávaného typu.
     *
     * @param mixed  $value      hodnota
     * @param string $sanitizeAs typ hodnoty int|string|float|null
     *
     * @return mixed
     */
    public static function sanitizeAsType($value, $sanitizeAs)
    {
        $sanitized = null;
        switch ($sanitizeAs) {
            case 'string':
                return (string) $value;
                break;
            case 'int':
                if (!strlen(trim($value))) {
                    return null;
                }
                return (int) $value;
                break;
            case 'float':
                return (float) $value;
                break;
            case 'bool':
            case 'boolean':
                switch ($value) {
                    case 'FALSE':
                    case 'false':
                    case false:
                    case 0:
                    case '':
                        $sanitized = false;
                        break;
                    case 1:
                    case true:
                    case 'true':
                    case 'TRUE':
                        $sanitized = true;
                        break;
                }
                break;
            case 'null':
            default:
                $sanitized = $value;
                break;
        }

        return $sanitized;
    }

    /**
     * Vrací hodnotu klíče prametru volání stránky.
     *
     * @param string $field      klíč POST nebo GET
     * @param string $sanitizeAs ošetřit vrácenou hodnotu jako float|int|string
     *
     * @return mixed
     */
    public function getRequestValue($field, $sanitizeAs = null)
    {
        $this->setupWebPage();
        if (isset($_REQUEST[$field])) {
            if (isset($this->webPage->requestValuesToKeep[$field])) {
                $this->webPage->requestValuesToKeep[$field] = $_REQUEST[$field];
            }
            if ($sanitizeAs) {
                return self::sanitizeAsType($_REQUEST[$field], $sanitizeAs);
            } else {
                return $_REQUEST[$field];
            }
        } else {
            if (isset($this->requestValuesToKeep[$field])) {
                if ($this->requestValuesToKeep[$field] !== true) {
                    return $this->requestValuesToKeep[$field];
                }
            }

            return;
        }
    }

    /**
     * Vrací hodnotu klíče pramatru volání stránky.
     *
     * @param string $field      klíč GET
     * @param string $sanitizeAs ošetřit vrácenou hodnotu jako float|int|string
     *
     * @return string
     */
    public static function getGetValue($field, $sanitizeAs = null)
    {
        if (isset($_GET[$field])) {
            if ($sanitizeAs) {
                return self::sanitizeAsType($_GET[$field], $sanitizeAs);
            } else {
                return $_GET[$field];
            }
        } else {
            return;
        }
    }

    /**
     * Vrací hodnotu klíče pramatru volání stránky.
     *
     * @param string $field      klíč POST
     * @param string $sanitizeAs ošetřit vrácenou hodnotu jako float|int|string
     *
     * @return string
     */
    public static function getPostValue($field, $sanitizeAs = null)
    {
        if (isset($_POST[$field])) {
            if ($sanitizeAs) {
                return self::sanitizeAsType($_POST[$field], $sanitizeAs);
            } else {
                return $_POST[$field];
            }
        } else {
            return;
        }
    }

    /**
     * Byla stránka zobrazena po odeslání formuláře metodou POST ?
     *
     * @category requestValue
     *
     * @return bool
     */
    public static function isFormPosted()
    {
        return isset($_POST) && count($_POST);
    }

    /**
     * Začne uchovávat hodnotu proměnné.
     *
     * @category requestValue
     *
     * @param string $varName  název klíče
     * @param mixed  $varValue hodnota klíče
     *
     * @return string Keeped key name
     */
    public function keepRequestValue($varName, $varValue = true)
    {
        Shared::webPage()->requestValuesToKeep[$varName] = $varValue;

        return $varName;
    }

    /**
     * Začne uchovávat hodnotu proměnných vyjmenovaných v poli.
     *
     * @category requestValue
     *
     * @param array $varNames asociativní pole hodnot
     */
    public function keepRequestValues($varNames)
    {
        if (is_array($varNames)) {
            foreach ($varNames as $varName => $varValue) {
                if (is_numeric($varName)) {
                    $varName = $varValue;
                    $varValue = $this->getRequestValue($varName);
                    if ($varValue) {
                        $this->keepRequestValue($varName, $varValue);
                    } else {
                        $this->keepRequestValue($varName, true);
                    }
                } else {
                    $this->keepRequestValue($varName, $varValue);
                }
            }
        }
    }

    /**
     * Zruší zachovávání hodnoty proměnné.
     *
     * @category requestValue
     *
     * @param string $varName jméno proměnné
     */
    public function unKeepRequestValue($varName)
    {
        unset(Shared::webPage()->requestValuesToKeep[$varName]);
    }

    /**
     * Zruší zachovávání hodnot proměnných.
     *
     * @category requestValue
     */
    public function unKeepRequestValues()
    {
        Shared::webPage()->requestValuesToKeep = [];
    }

    /**
     * Vrací fragment udrživaných hodnot pro link.
     *
     * @category requestValue
     *
     * @return string
     */
    public function getLinkParametersToKeep()
    {
        $requestValuesToKeep = Shared::webPage()->requestValuesToKeep;

        if (is_null($requestValuesToKeep) || !is_array($requestValuesToKeep) || !count($requestValuesToKeep)) {
            return '';
        }
        $ArgsToKeep = [];
        foreach ($requestValuesToKeep as $name => $value) {
            if (is_string($value) && strlen($value)) {
                $ArgsToKeep[$name] = $name.'='.$value;
            }
        }

        return implode('&amp;', $ArgsToKeep);
    }

    /**
     * Zapamatuje si odkaz na základní stránku webu.
     *
     * @param EaseWebPage|true $webPage Objekt stránky, true - force assign
     */
    public function setupWebPage(&$webPage = null)
    {
        if (is_null($webPage)) {
            $webPage = &$this;
        }

        if (!isset($this->webPage) || !is_object($this->webPage)) {
            $this->webPage = $webPage;
        }
    }

    /**
     * Nastaví formát výstupu.
     *
     * @param string $outputFormat výstupní formát, např Mail nebo Print
     */
    public function setOutputFormat($outputFormat)
    {
        $this->outputFormat = $outputFormat;
        foreach ($this->pageParts as $part) {
            $this->raise($part, ['OutputFormat']);
        }
    }

    /**
     * Vrací formát výstupu.
     */
    public function getOutputFormat()
    {
        return $this->outputFormat;
    }

    /**
     * Převezme hlášky z pole nebo objektu.
     *
     * @param mixed $msgSource zdroj zpráv - pole nebo EaseObjekt
     * @param array $denyQues  neprevezme tyto typy
     *
     * @return int počet převzatých hlášek
     */
    public function takeStatusMessages($msgSource, $denyQues = null)
    {
        if (is_array($msgSource) && count($msgSource)) {
            $allMessages = [];
            foreach ($msgSource as $quee => $messages) {
                if (is_array($denyQues) && in_array($quee, $denyQues)) {
                    continue;
                }
                foreach ($messages as $mesgID => $message) {
                    $allMessages[$mesgID][$quee] = $message;
                }
            }
            ksort($allMessages);
            foreach ($allMessages as $message) {
                $quee = key($message);
                $this->addStatusMessage(reset($message), $quee, false, false);
            }

            return count($msgSource);
        }
        if (is_object($msgSource)) {
            if (isset($msgSource->statusMessages) && count($msgSource->statusMessages)) {
                $msgTaken = count($msgSource->statusMessages);
                $this->addStatusMessages($msgSource->getStatusMessages(true));

                return $msgTaken;
            } else {
                if (isset($msgSource->webPage) && isset($msgSource->webPage->statusMessages)
                    && count($msgSource->webPage->statusMessages)) {
                    $msgTaken = count($msgSource->webPage->statusMessages);
                    $this->statusMessages = array_merge($this->statusMessages,
                        $msgSource->webPage->statusMessages);
                    $msgSource->webPage->statusMessages = [];

                    return $msgTaken;
                }
            }
        }

        return 0;
    }

    /**
     * Vrací pole jako parametry URL.
     *
     * @param array  $params
     * @param string $baseUrl
     */
    public static function arrayToUrlParams($params, $baseUrl = '')
    {
        if (strstr($baseUrl, '?')) {
            return $baseUrl.'&'.http_build_query($params);
        } else {
            return $baseUrl.'?'.http_build_query($params);
        }
    }
}
