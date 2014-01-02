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
    public $LogType = 'file';

    /**
     * Adresář do kterého se zapisují logy
     * @var string dirpath
     */
    public $LogPrefix = null;

    /**
     * Soubor s do kterého se zapisuje log
     * @var string
     */
    public $LogFileName = 'Ease.log';

    /**
     * úroveň logování
     * @var string - silent,debug
     */
    public $LogLevel = 'debug';

    /**
     * Soubor do kterého se zapisuje report
     * @var string filepath
     */
    public $ReportFile = 'EaseReport.log';

    /**
     * Soubor do kterého se lougují pouze zprávy typu Error
     * @var string  filepath
     */
    public $ErrorLogFile = 'EaseErrors.log';

    /**
     * Hodnoty pro obarvování logu
     * @var array
     */
    public $LogStyles = array(
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
    private $StoreMessages = false;

    /**
     * Pole uložených zpráv
     * @var array
     */
    private $StoredMessages = array();

    /**
     * ID naposledy ulozene zpravy
     * @var int unsigned
     */
    private $MessageID = 0;

    /**
     * Obecné konfigurace frameworku
     * @var EaseShared
     */
    public $EaseShared = null;

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
        if ($this->LogPrefix) {
            return;
        } else {
            if (defined('LOG_DIRECTORY')) {
                $this->LogPrefix = EaseBrick::sysFilename(constant('LOG_DIRECTORY'));
                if ($this->TestDirectory($this->LogPrefix)) {
                    $this->LogFileName = $this->LogPrefix . $this->LogFileName;
                    $this->ReportFile = $this->LogPrefix . $this->ReportFile;
                    $this->ErrorLogFile = $this->LogPrefix . $this->ErrorLogFile;
                } else {
                    $this->LogPrefix = null;
                    $this->LogFileName = null;
                    $this->ReportFile = null;
                    $this->ErrorLogFile = null;
                }
            } else {
                $this->LogType = 'none';
                $this->LogPrefix = null;
                $this->LogFileName = null;
                $this->ReportFile = null;
                $this->ErrorLogFile = null;
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
        $this->StoreMessages = $Check;
        if (is_bool($Check)) {
            $this->resetStoredMessages();
        }
    }

    /**
     * Resetne pole uložených zpráv
     */
    public function resetStoredMessages()
    {
        $this->StoredMessages = array();
    }

    /**
     * Vrací pole uložených zpráv
     *
     * @return array
     */
    public function getStoredMessages()
    {
        return $this->StoredMessages;
    }

    /**
     * Zapise zapravu do logu
     *
     * @param string $Caller  název volajícího objektu
     * @param string $Message zpráva
     * @param string $Type    typ zprávy (success|info|error|warning|*)
     *
     * @return bool byl report zapsán ?
     */
    public function addToLog($Caller, $Message, $Type = 'message')
    {
        $this->MessageID++;
        if (($this->LogLevel == 'silent') && ($Type != 'error')) {
            return;
        }
        if (($this->LogLevel != 'debug') && ( $Type == 'debug')) {
            return;
        }
        if ($this->StoreMessages) {
            $this->StoredMessages[$Type][$this->MessageID] = $Message;
        }

        $Message = htmlspecialchars_decode(strip_tags(stripslashes($Message)));

        $LogLine = date(DATE_ATOM) . ' (' . $Caller . ') ' . str_replace(array('notice', 'message', 'debug', 'report', 'error', 'warning', 'success', 'info', 'mail'), array('**', '##', '@@', '::'), $Type) . ' ' . $Message . "\n";
        if (!isset($this->LogStyles[$Type])) {
            $Type = 'notice';
        }
        if ($this->LogType == 'console' || $this->LogType == 'both') {
            if ($this->RunType == 'cgi') {
                echo $LogLine;
            } else {
                echo '<div style="' . $this->LogStyles[$Type] . '">' . $LogLine . "</div>\n";
                flush();
            }
        }
        if ($this->LogPrefix) {
            if ($this->LogType == 'file' || $this->LogType == 'both') {
                if ($this->LogFileName) {
                    if (!$this->_logFileHandle) {
                        $this->_logFileHandle = fopen($this->LogFileName, 'a+');
                    }
                    if ($this->_logFileHandle) {
                        fwrite($this->_logFileHandle, $LogLine);
                    }
                }
            }
            if ($Type == 'error') {
                if ($this->ErrorLogFile) {
                    if (!$this->_errorLogFileHandle) {
                        $this->_errorLogFileHandle = fopen($this->ErrorLogFile, 'a+');
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
     * @param string $NewLogFileName new log filename
     *
     * @return bool
     */
    public function renameLogFile($NewLogFileName)
    {
        $NewLogFileName = dirname($this->LogFileName) . '/' . basename($NewLogFileName);
        if (rename($this->LogFileName, $NewLogFileName)) {
            return realpath($NewLogFileName);
        } else {
            return realpath($this->LogFileName);
        }
    }

    /**
     * Detekuje a nastaví zdali je objekt suštěn jako script (cgi) nebo jako page (web)
     *
     * @param string $RunType force type
     *
     * @return string type
     */
    public function setRunType($RunType = null)
    {
        if (!$RunType) {
            if (isset($_SERVER['HTTP_HOST'])) {
                $this->RunType = 'web';
            } else {
                $this->RunType = 'cgi';
            }

            return $this->RunType;
        }
        if (($RunType != 'web') || ( $RunType != 'cgi')) {
            return null;
        } else {
            return $this->RunType;
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
        if ($this->ErrorLogFile) {
            $LogFileHandle = @fopen($this->ErrorLogFile, 'a+');
            if ($LogFileHandle) {
                if ($this->easeShared->RunType == 'web') {
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
                $this->addToLog('Error: Couldn\'t open the ' . realpath($this->ErrorLogFile) . ' error log file', 'error');
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

}
