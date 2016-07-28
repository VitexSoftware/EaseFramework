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
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 * @author    Vitex <vitex@hippy.cz>
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
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
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
    public $registry = [];

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
    public $User = null;

    /**
     * Odkaz na objekt databáze.
     *
     * @var SQL\PDO
     */
    public $dbLink = null;

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
     * Inicializace sdílené třídy.
     */
    public function __construct()
    {
        $this->setRunType();
        if (isset($_SESSION['EaseMessages'])) {
            $this->statusMessages = $_SESSION['EaseMessages'];
            unset($_SESSION['EaseMessages']);
        }
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
        $this->registry[$configName] = $configValue;
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
        if (isset($this->registry[$configName])) {
            return $this->registry[$configName];
        }

        return;
    }

    /**
     * Detekuje a nastaví zdali je objekt suštěn jako script (cgi) nebo jako page (web).
     *
     * @param string $runType force type
     *
     * @return string type
     */
    public function setRunType($runType = null)
    {
        if (!$runType) {
            if (self::isCli()) {
                $this->runType = 'cli';
            } else {
                $this->runType = 'web';
            }
        }
        if (($runType != 'web') || ($runType != 'cli')) {
            return;
        } else {
            return $this->runType;
        }
    }

    /**
     * Returns database object instance.
     *
     * @return SQL\PDO
     */
    public static function db()
    {
        return SQL\PDO::singleton();
    }

    /**
     * Vrací instanci objektu logování.
     *
     * @return Logger
     */
    public static function logger()
    {
        if (defined('LOG_NAME')) {
            return LoggerToSyslog::singleton();
        } elseif (defined('LOG_FILE')) {
            LoggerToFile::singleton();
        } else {
            return LoggerToMemory::singleton();
        }
    }

    /**
     * Vrací nebo registruje instanci webové stránky.
     *
     * @param EaseWebPage $oPage objekt webstránky k zaregistrování
     *
     * @return EaseWebPage
     */
    public static function &webPage($oPage = null)
    {
        $shared = self::instanced();
        if (is_object($oPage)) {
            $shared->webPage = &$oPage;
        }
        if (!is_object($shared->webPage)) {
            self::webPage(WebPage::singleton());
        }

        return $shared->webPage;
    }

    /**
     * Vrací, případně i založí objekt uživatele.
     *
     * @param User|Anonym|string $user objekt nového uživatele nebo
     *                                 název třídy
     *
     * @return User
     */
    public static function &user($user = null, $userSessionName = null)
    {
        if (is_null($user) && isset($_SESSION[self::$userSessionName]) && is_object($_SESSION[self::$userSessionName])) {
            return $_SESSION[self::$userSessionName];
        }

        if (!is_null($userSessionName)) {
            self::$userSessionName = $userSessionName;
        }
        if (is_object($user)) {
            $_SESSION[self::$userSessionName] = clone $user;
        } else {
            if (class_exists($user)) {
                $_SESSION[self::$userSessionName] = new $user();
            } elseif (!isset($_SESSION[self::$userSessionName]) || !is_object($_SESSION[self::$userSessionName])) {
                $_SESSION[self::$userSessionName] = new Anonym();
            }
        }

        return $_SESSION[self::$userSessionName];
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

    public function __destruct()
    {
        if (!self::isCli()) {
            $_SESSION['EaseMessages'] = $this->statusMessages;
        }
    }
}