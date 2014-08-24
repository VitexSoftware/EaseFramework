<?php

/**
 * Všeobecně sdílený objekt frameworku.
 * Tento objekt je automaticky přez svůj singleton instancován do každého Ease*
 * objektu.
 * Poskytuje kdykoliv přístup k často volaným objektům framworku jako například
 * uživatel, databáze, webstránka nebo logy.
 * Také obsahuje pole obecnych nastavení a funkce pro jeho obluhu.
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 * @author Vitex <vitex@hippy.cz>
 */
require_once 'EaseAtom.php';

/**
 * Všeobecně sdílený objekt frameworku.
 * Tento objekt je automaticky přez svůj singleton instancován do každého Ease*
 * objektu.
 * Poskytuje kdykoliv přístup k často volaným objektům framworku jako například
 * uživatel, databáze, webstránka nebo logy.
 * Také obsahuje pole obecnych nastavení a funkce pro jeho obluhu.
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 * @author Vitex <vitex@hippy.cz>
 */
class EaseShared extends EaseAtom {

    /**
     * Odkaz na objekt stránky
     * @var EaseWebPage
     */
    public $webPage = null;

    /**
     * JavaScripty
     * @var array
     */
    public $javaScripts = null;

    /**
     * Pole kaskádových stylů
     * $var array
     */
    public $cascadeStyles = null;

    /**
     * Pole konfigurací
     * @var array
     */
    public $registry = array();

    /**
     * Informuje zdali je objekt spuštěn v prostředí webové stránky nebo jako script
     * @var string web|cli
     */
    public $runType = null;

    /**
     * Odkaz na instanci objektu uživatele
     * @var EaseUser|EaseAnonym
     */
    public $User = null;

    /**
     * Odkaz na objekt databáze
     * @var EaseDbMySqli
     */
    public $myDbLink = null;

    /**
     * Saves obejct instace (singleton...)
     * @var EaseShared
     */
    private static $_instance = null;

    /**
     * Pole odkazů na všechny vložené objekty
     * @var array pole odkazů
     */
    public $allItems = array();

    /**
     * Název položky session s objektem uživatele
     * @var string
     */
    public static $userSessionName = 'User';

    /**
     * Inicializace sdílené třídy
     */
    public function __construct() {
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
     * @link http://docs.php.net/en/language.oop5.patterns.html Dokumentace a priklad
     * @return EaseWebPage
     */
    public static function singleton($class = null) {
        if (!isset(self::$_instance)) {
            if (is_null($class)) {
                $class = __CLASS__;
            }
            self::$_instance = new $class();
        }

        return self::$_instance;
    }

    /**
     * Vrací se
     *
     * @return EaseShared
     */
    public static function & instanced() {
        $easeShared = EaseShared::singleton();

        return $easeShared;
    }

    /**
     * Nastavuje hodnotu konfiguračního klíče
     *
     * @param string $configName  klíč
     * @param mixed  $configValue hodnota klíče
     */
    public function setConfigValue($configName, $configValue) {
        $this->registry[$configName] = $configValue;
    }

    /**
     * Vrací konfigurační hodnotu pod klíčem
     *
     * @param string $configName klíč
     *
     * @return mixed
     */
    public function getConfigValue($configName) {
        if (isset($this->registry[$configName])) {
            return $this->registry[$configName];
        }

        return null;
    }

    /**
     * Detekuje a nastaví zdali je objekt suštěn jako script (cgi) nebo jako page (web)
     *
     * @param string $runType force type
     *
     * @return string type
     */
    public function setRunType($runType = null) {
        if (!$runType) {
            if (self::isCli()) {
                $this->runType = 'cli';
            } else {
                $this->runType = 'web';
            }
        }
        if (($runType != 'web') || ( $runType != 'cli')) {
            return null;
        } else {
            return $this->runType;
        }
    }

    /**
     * Vrací instanci objektu databáze MySQL
     *
     * @return EaseDbMySqli
     */
    public static function myDbLink() {
        if (!class_exists('EaseDbMySqli')) {
            include 'EaseMySQL.php';
        }

        return EaseDbMySqli::singleton();
    }

    /**
     * Vrací instanci objektu databáze MSSQL
     *
     * @return EaseDbMSSQL
     */
    public static function msDbLink() {
        if (!class_exists('EaseDbMSSQL')) {
            include_once 'EaseMSSQL.php';
        }

        return EaseDbMSSQL::singleton();
    }

    /**
     * Vrací instanci objektu logování
     *
     * @return EaseLogger
     */
    public static function logger() {
        return EaseLogger::singleton();
    }

    /**
     * Vrací nebo registruje instanci webové stránky
     *
     * @param  EaseWebPage $oPage objekt webstránky k zaregistrování
     * @return EaseWebPage
     */
    static function &webPage($oPage = null) {
        $shared = EaseShared::instanced();
        if (is_object($oPage)) {
            $shared->webPage = & $oPage;
        }
        if (!is_object($shared->webPage)) {
            require_once 'EaseWebPage.php';
            EaseShared::webPage(EaseWebPage::singleton());
        }

        return $shared->webPage;
    }

    /**
     * Vrací, případně i založí objekt uživatele
     *
     * @param EaseUser|EaseAnonym|string $user objekt nového uživatele nebo 
     *                                         název třídy
     *
     * @return EaseUser
     */
    public static function & user($user = NULL, $userSessionName = NULL) {
        if (isset($_SESSION[self::$userSessionName]) && is_object($_SESSION[self::$userSessionName])) {
            return $_SESSION[self::$userSessionName];
        }

        if (!is_null($userSessionName)) {
            self::$userSessionName = $userSessionName;
        }
        if (is_object($user)) {
            $_SESSION[self::$userSessionName] = clone $user;
        } else {
            $_SESSION[self::$userSessionName] = new $user;
        }
        if (!isset($_SESSION[self::$userSessionName])) {
            require_once 'Ease/EaseUser.php';
            $_SESSION[self::$userSessionName] = new EaseAnonym();
        }

        return $_SESSION[self::$userSessionName];
    }

    /**
     * Běží php v příkazovém řádku ?
     * @return boolean
     */
    public static function isCli() {
        return (PHP_SAPI == 'cli' && empty($_SERVER['REMOTE_ADDR']));
    }

    /**
     * Vrací DB objekt Pearu
     *
     * @return EasePearDBCompatible
     */
    public static function dbCompatible() {
        return new EasePearDBCompatible();
    }

    /**
     * Zaregistruje položku k finalizaci
     *
     * @param mixed $ItemPointer
     */
    public static function registerItem(&$ItemPointer) {
        $EaseShared = EaseShared::singleton();
        $EaseShared->allItems[] = $ItemPointer;
    }

}
