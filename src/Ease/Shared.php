<?php
/**
 * Všeobecně sdílený objekt frameworku.
 * Tento objekt je automaticky přez svůj singleton instancován do každého Ease*
 * objektu.
 * Poskytuje kdykoliv přístup k často volaným objektům framworku jako například
 * uživatel, databáze, webstránka nebo logy.
 * Také obsahuje pole obecnych nastavení a funkce pro jeho obluhu.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2018 Vitex@hippy.cz (G)
 */

namespace Ease;

/**
 * Všeobecně sdílený objekt frameworku.
 * Tento objekt je automaticky přez svůj singleton instancován do každého Ease*
 * objektu.
 * Poskytuje kdykoliv přístup k často volaným objektům framworku jako například
 * uživatel, databáze, webstránka nebo logy.
 * Také obsahuje pole obecnych nastavení a funkce pro jeho obluhu.
 *
 * @copyright 2009-2016 Vitex@hippy.cz (G)
 * @author    Vitex <vitex@hippy.cz>
 */
class Shared extends Atom
{
    /**
     * Odkaz na objekt stránky.
     *
     * @var WebPage
     */
    public $webPage = null;

    /**
     * JavaScripts.
     *
     * @var array
     */
    public $javaScripts = null;

    /**
     * Pole kaskádových stylů
     * $var array.
     */
    public $cascadeStyles = null;

    /**
     * Pole konfigurací.
     *
     * @var array
     */
    public $configuration = [];

    /**
     * Informuje zdali je objekt spuštěn v prostředí webové stránky nebo jako script.
     *
     * @var string web|cli
     */
    public $runType = null;

    /**
     * Odkaz na instanci objektu uživatele.
     *
     * @var User|Anonym
     */
    public $user = null;

    /**
     * Odkaz na objekt databáze.
     *
     * @var SQL\PDO
     */
    public $dbLink = null;

    /**
     *
     * @var Locale 
     */
    public $locale = null;

    /**
     * Saves obejct instace (singleton...).
     *
     * @var Shared
     */
    private static $_instance = null;

    /**
     * Pole odkazů na všechny vložené objekty.
     *
     * @var array pole odkazů
     */
    public $allItems = [];

    /**
     * Název položky session s objektem uživatele.
     *
     * @var string
     */
    public static $userSessionName = 'User';

    /**
     * Logger live here
     * @var Logger\ToFile|Logger\ToMemory|Logger\ToSyslog
     */
    static public $log = null;

    /**
     * Array of Status Messages
     * @var array of Logger\Message
     */
    public $messages = [];

    /**
     * Inicializace sdílené třídy.
     */
    public function __construct()
    {
        $cgiMessages = [];
        $webMessages = [];
        $prefix      = defined('EASE_APPNAME') ? constant('EASE_APPNAME') : '';
        $msgFile     = sys_get_temp_dir().'/'.$prefix.'EaseStatusMessages.ser';
        if (file_exists($msgFile) && is_readable($msgFile) && filesize($msgFile)
            && is_writable($msgFile)) {
            $cgiMessages = unserialize(file_get_contents($msgFile));
            file_put_contents($msgFile, '');
        }

        if (defined('EASE_APPNAME')) {
            if (isset($_SESSION[constant('EASE_APPNAME')]['EaseMessages'])) {
                $webMessages = $_SESSION[constant('EASE_APPNAME')]['EaseMessages'];
                unset($_SESSION[constant('EASE_APPNAME')]['EaseMessages']);
            }
        } else {
            if (isset($_SESSION['EaseMessages'])) {
                $webMessages = $_SESSION['EaseMessages'];
                unset($_SESSION['EaseMessages']);
            }
        }
        $this->statusMessages = is_array($cgiMessages) ? array_merge($cgiMessages,
                $webMessages) : $webMessages;
    }

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako konstruktor)
     * se bude v ramci behu programu pouzivat pouze jedna jeho Instance (ta prvni).
     *
     * @param string $class název třídy jenž má být zinstancována
     *
     * @link   http://docs.php.net/en/language.oop5.patterns.html Dokumentace a priklad
     *
     * @return \Ease\Shared
     */
    public static function singleton($class = null)
    {
        if (!isset(self::$_instance)) {
            if (is_null($class)) {
                $class = __CLASS__;
            }
            self::$_instance = new $class();
        }

        return self::$_instance;
    }

    /**
     * Vrací se.
     *
     * @return Shared
     */
    public static function &instanced()
    {
        $easeShared = self::singleton();

        return $easeShared;
    }

    /**
     * Nastavuje hodnotu konfiguračního klíče.
     *
     * @param string $configName  klíč
     * @param mixed  $configValue hodnota klíče
     */
    public function setConfigValue($configName, $configValue)
    {
        $this->configuration[$configName] = $configValue;
    }

    /**
     * Vrací konfigurační hodnotu pod klíčem.
     *
     * @param string $configName klíč
     *
     * @return mixed
     */
    public function getConfigValue($configName)
    {
        return array_key_exists($configName, $this->configuration) ? $this->configuration[$configName]
                : null;
    }

    /**
     * Returns database object instance.
     *
     * @return SQL\PDO
     */
    public static function &db($pdo = null)
    {
        $shared = self::instanced();
        if (is_object($pdo)) {
            $shared->dbLink = &$pdo;
        }
        if (!is_object($shared->dbLink)) {
            $shared->dbLink = self::db(SQL\PDO::singleton(is_array($pdo) ? $pdo : [
                ]));
        }

        return $shared->dbLink;
    }

    /**
     * Vrací instanci objektu logování.
     *
     * @return Logger
     */
    public static function logger()
    {
        return Logger\Regent::singleton();
    }

    /**
     * Vrací nebo registruje instanci webové stránky.
     *
     * @param WebPage $oPage objekt webstránky k zaregistrování
     *
     * @return WebPage
     */
    public static function &webPage($oPage = null)
    {
        $shared = self::instanced();
        if (is_object($oPage)) {
            $shared->webPage = &$oPage;
        }
        if (!is_object($shared->webPage)) {
            $shared->webPage = WebPage::singleton();
        }
        return $shared->webPage;
    }

    /**
     * Locale Class handler
     * 
     * @param Locale $locale overriding object
     * 
     * @return Locale
     */
    public static function &locale($locale = null)
    {
        $shared = self::instanced();
        if (is_object($locale)) {
            $shared->locale = &$locale;
        }
        if (!is_object($shared->locale)) {
            $shared->locale = Locale::singleton();
        }
        return $shared->locale;
    }

    /**
     * Vrací, případně i založí objekt uživatele.
     *
     * @param User|Anonym|string $user objekt nového uživatele nebo
     *                                 název třídy
     *
     * @return User
     */
    public static function &user($user = null, $userSessionName = 'User')
    {
        $efprefix = defined('EASE_APPNAME') ? constant('EASE_APPNAME') : 'EaseFramework';

        if (is_null($user) && isset($_SESSION[$efprefix][self::$userSessionName])
            && is_object($_SESSION[$efprefix][self::$userSessionName])) {
            return $_SESSION[$efprefix][self::$userSessionName];
        }

        if (!is_null($userSessionName)) {
            self::$userSessionName = $userSessionName;
        }
        if (is_object($user)) {
            $_SESSION[$efprefix][self::$userSessionName] = clone $user;
        } else {
            if (class_exists($user)) {
                $_SESSION[$efprefix][self::$userSessionName] = new $user();
            } elseif (!isset($_SESSION[$efprefix][self::$userSessionName]) || !is_object($_SESSION[$efprefix][self::$userSessionName])) {
                $_SESSION[$efprefix][self::$userSessionName] = new Anonym();
            }
        }

        return $_SESSION[$efprefix][self::$userSessionName];
    }

    /**
     * Běží php v příkazovém řádku ?
     *
     * @return bool
     */
    public static function isCli()
    {
        return PHP_SAPI == 'cli' && empty($_SERVER['REMOTE_ADDR']);
    }

    /**
     * Zaregistruje položku k finalizaci.
     *
     * @param mixed $itemPointer
     */
    public static function registerItem(&$itemPointer)
    {
        $easeShared             = self::singleton();
        $easeShared->allItems[] = $itemPointer;
    }

    /**
     * Take message to print / log
     * @param Logger\Message $message
     */
    public function takeMessage($message)
    {
        $this->messages[] = $message;
        $this->addStatusMessage($message->body, $message->type);
        $this->logger()->addToLog($message->caller, $message->body,
            $message->type);
    }

    /**
     * Write remaining messages to temporary file.
     */
    public function __destruct()
    {
        if (self::isCli()) {
            $prefix       = defined('EASE_APPNAME') ? constant('EASE_APPNAME') : '';
            $messagesFile = sys_get_temp_dir().'/'.$prefix.'EaseStatusMessages.ser';
            file_put_contents($messagesFile, serialize($this->statusMessages));
        } else {
            if (defined('EASE_APPNAME')) {
                $_SESSION[constant('EASE_APPNAME')]['EaseMessages'] = $this->statusMessages;
            } else {
                $_SESSION['EaseMessages'] = $this->statusMessages;
            }
        }
    }

    /**
     * Load Configuration values from json file $this->configFile and define UPPERCASE keys
     *
     * @param string  $configFile      Path to file with configuration
     * @param boolean $defineConstants false to do not define constants
     *
     * @return array full configuration array
     */
    public function loadConfig($configFile, $defineConstants = false)
    {
        if (!file_exists($configFile)) {
            throw new Exception('Config file '.(realpath($configFile) ? realpath($configFile)
                    : $configFile).' does not exist');
        }
        $configuration = json_decode(file_get_contents($configFile), true);
        if (empty($configuration)) {
            $this->addStatusMessage('Empty Config File '.realpath($configFile) ? realpath($configFile)
                        : $configFile, 'debug');
        } else {
            foreach ($configuration as $configKey => $configValue) {
                if ($defineConstants && (strtoupper($configKey) == $configKey) && (!defined($configKey))) {
                    define($configKey, $configValue);
                } else {
                    $this->setConfigValue($configKey, $configValue);
                }
                $this->configuration[$configKey] = $configValue;
            }
        }

        if (array_key_exists('debug', $this->configuration)) {
            $this->debug = boolval($this->configuration['debug']);
        }

        return $this->configuration;
    }

    /**
     * Initialise Gettext
     *
     * $i18n/$defaultLocale/LC_MESSAGES/$appname.mo
     *
     * @deprecated since version 1.5 - Moved to EaseBricks
     * 
     * @param string $appname        name for binddomain
     * @param string $defaultLocale  locale of source code localstring
     * @param string $i18n           directory base localisation directory
     *
     * @return
     */
    public static function initializeGetText($appname, $defaultLocale = 'en_US',
                                             $i18n = '../i18n')
    {
        return self::instanced()->locale(new Locale($defaultLocale, $i18n,
                $appname));
    }

    /**
     * Add params to url
     *
     * @param string  $url      originall url
     * @param array   $addParams   value to add
     * @param boolean $override replace already existing values ?
     * 
     * @return string url with parameters added
     */
    public static function addUrlParams($url, $addParams, $override = false)
    {
        $urlParts = parse_url($url);
        $urlFinal = '';
        if (array_key_exists('scheme', $urlParts)) {
            $urlFinal .= $urlParts['scheme'].'://'.$urlParts['host'];
        }
        if (array_key_exists('port', $urlParts)) {
            $urlFinal .= ':'.$urlParts['port'];
        }
        if (array_key_exists('path', $urlParts)) {
            $urlFinal .= $urlParts['path'];
        }
        if (array_key_exists('query', $urlParts)) {
            parse_str($urlParts['query'], $queryUrlParams);
            $urlParams = $override ? array_merge($queryUrlParams, $addParams) : array_merge($addParams,
                    $queryUrlParams);
        } else {
            $urlParams = $addParams;
        }

        if (!empty($urlParams)) {
            $urlFinal .= '?';
            if (is_array($urlParams)) {
                $urlFinal .= http_build_query($urlParams);
            } else {
                $urlFinal .= $urlParams;
            }
        }
        return $urlFinal;
    }

    /**
     * Turn all URLs in clickable links.
     * 
     * @author Arnold Daniels <arnold@jasny.net>
     * 
     * @param string $value
     * @param array  $protocols  http/https, ftp, mail, twitter
     * @param array  $attributes
     * @param string $mode       normal or all
     * 
     * @return string
     */
    public static function linkify($value, $protocols = array('http', 'mail'),
                                   array $attributes = array())
    {
        // Link attributes
        $attr = '';
        foreach ($attributes as $key => $val) {
            $attr = ' '.$key.'="'.htmlentities($val).'"';
        }

        $links = array();

        // Extract existing links and tags
        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i',
            function ($match) use (&$links) {
            return '<'.array_push($links, $match[1]).'>';
        }, $value);

        // Extract text links for each protocol
        foreach ((array) $protocols as $protocol) {
            switch ($protocol) {
                case 'http':
                case 'https': $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i',
                        function ($match) use ($protocol, &$links, $attr) {
                        if ($match[1]) $protocol = $match[1];
                        $link     = $match[2] ?: $match[3];
                        return '<'.array_push($links,
                                "<a $attr href=\"$protocol://$link\">$link</a>").'>';
                    }, $value);
                    break;
                case 'mail': $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~',
                        function ($match) use (&$links, $attr) {
                        return '<'.array_push($links,
                                "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>").'>';
                    }, $value);
                    break;
                case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~',
                        function ($match) use (&$links, $attr) {
                        return '<'.array_push($links,
                                "<a $attr href=\"https://twitter.com/".($match[0][0]
                                == '@' ? '' : 'search/%23').$match[1]."\">{$match[0]}</a>").'>';
                    }, $value);
                    break;
                default: $value = preg_replace_callback('~'.preg_quote($protocol,
                            '~').'://([^\s<]+?)(?<![\.,:])~i',
                        function ($match) use ($protocol, &$links, $attr) {
                        return '<'.array_push($links,
                                "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>").'>';
                    }, $value);
                    break;
            }
        }

        // Insert all link
        return preg_replace_callback('/<(\d+)>/',
            function ($match) use (&$links) {
            return $links[$match[1] - 1];
        }, $value);
    }
}
