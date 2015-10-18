<?php

/**
 * Třída pro logování
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
require_once 'EaseAtom.php';

/**
 * Třída pro logování
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
class EaseLogger extends EaseAtom
{

    /**
     * Předvolená metoda logování
     * @var string
     */
    public $logType = 'file';

    /**
     * Adresář do kterého se zapisují logy
     * @var string dirpath
     */
    public $logPrefix = null;

    /**
     * Soubor s do kterého se zapisuje log
     * @var string
     */
    public $logFileName = 'Ease.log';

    /**
     * úroveň logování
     * @var string - silent,debug
     */
    public $logLevel = 'debug';

    /**
     * Soubor do kterého se zapisuje report
     * @var string filepath
     */
    public $reportFile = 'EaseReport.log';

    /**
     * Soubor do kterého se lougují pouze zprávy typu Error
     * @var string  filepath
     */
    public $errorLogFile = 'EaseErrors.log';

    /**
     * Hodnoty pro obarvování logu
     * @var array
     */
    public $logStyles = array(
      'notice' => 'color: black;',
      'success' => 'color: #2C5F23;',
      'message' => 'color: #2C5F23;',
      'warning' => 'color: #AB250E;',
      'error' => 'color: red;',
      'debug' => 'font-style: italic;',
      'report' => 'font-wight: bold;',
      'info' => 'color: blue;'
    );

    /**
     * Odkaz na vlastnící objekt
     * @var EaseSand ||
     */
    public $parentObject = null;

    /**
     * Filedescriptor Logu
     * @var resource
     */
    private $_logFileHandle = null;

    /**
     * Filedescriptor chybového Logu
     * @var resource
     */
    private $_errorLogFileHandle = null;

    /**
     * Ukládat Zprávy do pole;
     * @var boolean
     */
    private $storeMessages = false;

    /**
     * Pole uložených zpráv
     * @var array
     */
    private $storedMessages = array();

    /**
     * ID naposledy ulozene zpravy
     * @var int unsigned
     */
    private $messageID = 0;

    /**
     * Obecné konfigurace frameworku
     * @var EaseShared
     */
    public $easeShared = null;

    /**
     * Saves obejct instace (singleton...)
     */
    private static $_instance = null;

    /**
     * Logovací třída
     *
     * @param string $BaseLogDir
     */
    public function __construct($BaseLogDir = null)
    {
        $this->easeShared = EaseShared::singleton();
        $this->setupLogFiles();
    }

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako
     * konstruktor) se bude v ramci behu programu pouzivat pouze jedna jeho
     * instance (ta prvni).
     *
     * @link http://docs.php.net/en/language.oop5.patterns.html Dokumentace a
     * priklad
     */
    public static function singleton()
    {
        if (!isset(self::$_instance)) {
            $Class = __CLASS__;
            self::$_instance = new $Class();
        }

        return self::$_instance;
    }

    /**
     * Nastaví cesty logovacích souborů
     */
    public function setupLogFiles()
    {
        if ($this->logPrefix) {
            return;
        } else {
            if (defined('LOG_DIRECTORY')) {
                $this->logPrefix = EaseBrick::sysFilename(constant('LOG_DIRECTORY'));
                if ($this->TestDirectory($this->logPrefix)) {
                    $this->logFileName = $this->logPrefix . $this->logFileName;
                    $this->reportFile = $this->logPrefix . $this->reportFile;
                    $this->errorLogFile = $this->logPrefix . $this->errorLogFile;
                } else {
                    $this->logPrefix = null;
                    $this->logFileName = null;
                    $this->reportFile = null;
                    $this->errorLogFile = null;
                }
            } else {
                $this->logType = 'none';
                $this->logPrefix = null;
                $this->logFileName = null;
                $this->reportFile = null;
                $this->errorLogFile = null;
            }
        }
    }

    /**
     * Povolí nebo zakáže ukládání zpráv
     *
     * @param type $Check
     */
    public function setStoreMessages($Check)
    {
        $this->storeMessages = $Check;
        if (is_bool($Check)) {
            $this->resetStoredMessages();
        }
    }

    /**
     * Resetne pole uložených zpráv
     */
    public function resetStoredMessages()
    {
        $this->storedMessages = array();
    }

    /**
     * Vrací pole uložených zpráv
     *
     * @return array
     */
    public function getStoredMessages()
    {
        return $this->storedMessages;
    }

    /**
     * Zapise zapravu do logu
     *
     * @param string $caller  název volajícího objektu
     * @param string $message zpráva
     * @param string $type    typ zprávy (success|info|error|warning|*)
     *
     * @return bool byl report zapsán ?
     */
    public function addToLog($caller, $message, $type = 'message')
    {
        $this->messageID++;
        if (($this->logLevel == 'silent') && ($type != 'error')) {
            return;
        }
        if (($this->logLevel != 'debug') && ( $type == 'debug')) {
            return;
        }
        if ($this->storeMessages) {
            $this->storedMessages[$type][$this->messageID] = $message;
        }

        $message = htmlspecialchars_decode(strip_tags(stripslashes($message)));

        $LogLine = date(DATE_ATOM) . ' (' . $caller . ') ' . str_replace(array('notice', 'message', 'debug', 'report', 'error', 'warning', 'success', 'info', 'mail'), array('**', '##', '@@', '::'), $type) . ' ' . $message . "\n";
        if (!isset($this->logStyles[$type])) {
            $type = 'notice';
        }
        if ($this->logType == 'console' || $this->logType == 'both') {
            if ($this->runType == 'cgi') {
                echo $LogLine;
            } else {
                echo '<div style="' . $this->logStyles[$type] . '">' . $LogLine . "</div>\n";
                flush();
            }
        }
        if ($this->logPrefix) {
            if ($this->logType == 'file' || $this->logType == 'both') {
                if ($this->logFileName) {
                    if (!$this->_logFileHandle) {
                        $this->_logFileHandle = fopen($this->logFileName, 'a+');
                    }
                    if ($this->_logFileHandle) {
                        fwrite($this->_logFileHandle, $LogLine);
                    }
                }
            }
            if ($type == 'error') {
                if ($this->errorLogFile) {
                    if (!$this->_errorLogFileHandle) {
                        $this->_errorLogFileHandle = fopen($this->errorLogFile, 'a+');
                    }
                    if ($this->_errorLogFileHandle) {
                        fputs($this->_errorLogFileHandle, $LogLine);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Přejmenuje soubor s logem
     *
     * @param string $newLogFileName new log filename
     *
     * @return bool
     */
    public function renameLogFile($newLogFileName)
    {
        $newLogFileName = dirname($this->logFileName) . '/' . basename($newLogFileName);
        if (rename($this->logFileName, $newLogFileName)) {
            return realpath($newLogFileName);
        } else {
            return realpath($this->logFileName);
        }
    }

    /**
     * Detekuje a nastaví zdali je objekt suštěn jako script (cgi) nebo jako page (web)
     *
     * @param string $runType force type
     *
     * @return string type
     */
    public function setRunType($runType = null)
    {
        if (!$runType) {
            if (isset($_SERVER['HTTP_HOST'])) {
                $this->runType = 'web';
            } else {
                $this->runType = 'cgi';
            }

            return $this->runType;
        }
        if (($runType != 'web') || ( $runType != 'cgi')) {
            return null;
        } else {
            return $this->runType;
        }
    }

    /**
     * Zkontroluje stav adresáře a upozorní na případné nesnáze
     *
     * @param string  $DirectoryPath cesta k adresáři
     * @param boolean $IsDir         detekovat existenci adresáře
     * @param boolean $IsReadable    testovat čitelnost
     * @param boolean $IsWritable    testovat zapisovatelnost
     * @param boolean $LogToFile     povolí logování do souboru
     *
     * @return boolean konečný výsledek testu
     */
    public static function testDirectory($DirectoryPath, $IsDir = true, $IsReadable = true, $IsWritable = true, $LogToFile = false)
    {
        $Sanity = true;
        if ($IsDir) {
            if (!is_dir($DirectoryPath)) {
                echo ($DirectoryPath . _(' není složka. Jsem v adresáři:') . ' ' . getcwd());
                if ($LogToFile) {
                    $this->addToLog('TestDirectory', $DirectoryPath . _(' není složka. Jsem v adresáři:') . ' ' . getcwd());
                }
                $Sanity = false;
            }
        }
        if ($IsReadable) {
            if (!is_readable($DirectoryPath)) {
                echo ($DirectoryPath . _(' není čitelná složka. Jsem v adresáři:') . ' ' . getcwd());
                if ($LogToFile) {
                    $this->addToLog('TestDirectory', $DirectoryPath . _(' není čitelná složka. Jsem v adresáři:') . ' ' . getcwd());
                }
                $Sanity = false;
            }
        }
        if ($IsWritable) {
            if (!is_writable($DirectoryPath)) {
                echo ($DirectoryPath . _(' není zapisovatelná složka. Jsem v adresáři:') . ' ' . getcwd());
                if ($LogToFile) {
                    $this->addToLog('TestDirectory', $DirectoryPath . _(' není zapisovatelná složka. Jsem v adresáři:') . ' ' . getcwd());
                }

                $Sanity = false;
            }
        }

        return $Sanity;
    }

    /**
     * Oznamuje chybovou událost
     *
     * @param string $Caller     název volající funkce, nebo objektu
     * @param string $Message    zpráva
     * @param mixed  $ObjectData data k zaznamenání
     */
    public function error($Caller, $Message, $ObjectData = null)
    {
        if ($this->errorLogFile) {
            $LogFileHandle = @fopen($this->errorLogFile, 'a+');
            if ($LogFileHandle) {
                if ($this->easeShared->runType == 'web') {
                    fputs($LogFileHandle, EaseBrick::printPreBasic($_SERVER) . "\n #End of Server enviroment  <<<<<<<<<<<<<<<<<<<<<<<<<<< # \n\n");
                } else {
                    fputs($LogFileHandle, EaseBrick::printPreBasic($_ENV) . "\n #End of CLI enviroment  <<<<<<<<<<<<<<<<<<<<<<<<<<< # \n\n");
                }
                if (isset($_POST) && count($_POST)) {
                    fputs($LogFileHandle, EaseBrick::printPreBasic($_POST) . "\n #End of _POST  <<<<<<<<<<<<<<<<<<<<<<<<<<< # \n\n");
                }
                if (isset($_GET) && count($_GET)) {
                    fputs($LogFileHandle, EaseBrick::printPreBasic($_GET) . "\n #End of _GET enviroment  <<<<<<<<<<<<<<<<<<<<<<<<<<< # \n\n");
                }
                if ($ObjectData) {
                    fputs($LogFileHandle, EaseBrick::printPreBasic($ObjectData) . "\n #End of ObjectData >>>>>>>>>>>>>>>>>>>>>>>>>>>># \n\n");
                }
                fclose($LogFileHandle);
            } else {
                $this->addToLog('Error: Couldn\'t open the ' . realpath($this->errorLogFile) . ' error log file', 'error');
            }
        }
        $this->addToLog($Caller, $Message, 'error');
    }

    /**
     * Uzavře chybové soubory
     */
    public function __destruct()
    {
        if ($this->_logFileHandle) {
            fclose($this->_logFileHandle);
        }
        if ($this->_errorLogFileHandle) {
            fclose($this->_errorLogFileHandle);
        }
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
        if (key_exists($logType, $this->logStyles)) {
            return $this->logStyles[$logType];
        } else {
            return '';
        }
    }

}
