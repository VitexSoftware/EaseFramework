<?php
/**
 * Třída pro logování.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2016 Vitex@hippy.cz (G)
 */

namespace Ease\Logger;

class ToFile extends ToMemory
{
    /**
     * Předvolená metoda logování.
     *
     * @var string
     */
    public $logType = 'file';

    /**
     * Adresář do kterého se zapisují logy.
     *
     * @var string dirpath
     */
    public $logPrefix = null;

    /**
     * Soubor s do kterého se zapisuje log.
     *
     * @var string
     */
    public $logFileName = 'Ease.log';

    /**
     * úroveň logování.
     *
     * @var string - silent,debug
     */
    public $logLevel = 'debug';

    /**
     * Soubor do kterého se zapisuje report.
     *
     * @var string filepath
     */
    public $reportFile = 'EaseReport.log';

    /**
     * Soubor do kterého se lougují pouze zprávy typu Error.
     *
     * @var string filepath
     */
    public $errorLogFile = 'EaseErrors.log';

    /**
     * Odkaz na vlastnící objekt.
     *
     * @var EaseSand ||
     */
    public $parentObject = null;

    /**
     * Filedescriptor Logu.
     *
     * @var resource
     */
    private $_logFileHandle = null;

    /**
     * Filedescriptor chybového Logu.
     *
     * @var resource
     */
    private $_errorLogFileHandle = null;

    /**
     * ID naposledy ulozene zpravy.
     *
     * @var int unsigned
     */
    private $messageID = 0;

    /**
     * Obecné konfigurace frameworku.
     *
     * @var Shared
     */
    public $easeShared = null;

    /**
     * Saves obejct instace (singleton...).
     */
    private static $_instance = null;

    /**
     * Logovací třída.
     *
     * @param string $baseLogDir
     */
    public function __construct($baseLogDir = null)
    {
        $this->easeShared = \Ease\Shared::singleton();
        $this->setupLogFiles($baseLogDir);
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
            $Class           = __CLASS__;
            self::$_instance = new $Class();
        }

        return self::$_instance;
    }

    /**
     * Nastaví cesty logovacích souborů.
     * @param string $baseLogDir
     */
    public function setupLogFiles($baseLogDir = null)
    {
        if (is_null($baseLogDir)) {
            $baseLogDir = $this->logPrefix;
        }

        if (is_null($baseLogDir) && defined('LOG_DIRECTORY')) {
            $baseLogDir = constant('LOG_DIRECTORY');
        }

        if (!empty($baseLogDir)) {
            $this->logPrefix = \Ease\Brick::sysFilename($baseLogDir);
            if ($this->TestDirectory($this->logPrefix)) {
                $this->logFileName  = $this->logPrefix.$this->logFileName;
                $this->reportFile   = $this->logPrefix.$this->reportFile;
                $this->errorLogFile = $this->logPrefix.$this->errorLogFile;
            } else {
                $this->logPrefix    = null;
                $this->logFileName  = null;
                $this->reportFile   = null;
                $this->errorLogFile = null;
            }
        } else {
            $this->logType      = 'none';
            $this->logPrefix    = null;
            $this->logFileName  = null;
            $this->reportFile   = null;
            $this->errorLogFile = null;
        }
    }

    /**
     * Zapise zapravu do logu.
     *
     * @param string $caller  název volajícího objektu
     * @param string $message zpráva
     * @param string $type    typ zprávy (success|info|error|warning|*)
     *
     * @return null|boolean byl report zapsán ?
     */
    public function addToLog($caller, $message, $type = 'message')
    {
        ++$this->messageID;
        if (($this->logLevel == 'silent') && ($type != 'error')) {
            return;
        }
        if (($this->logLevel != 'debug') && ($type == 'debug')) {
            return;
        }
        $this->statusMessages[$type][$this->messageID] = $message;

        $message = htmlspecialchars_decode(strip_tags(stripslashes($message)));

        $logLine = date(DATE_ATOM).' ('.$caller.') '.str_replace(['notice', 'message',
                'debug', 'report', 'error', 'warning', 'success', 'info', 'mail',],
                ['**', '##', '@@', '::'], $type).' '.$message."\n";
        if (!isset($this->logStyles[$type])) {
            $type = 'notice';
        }
        if ($this->logType == 'console' || $this->logType == 'both') {
            if ($this->runType == 'cgi') {
                echo $logLine;
            } else {
                echo '<div style="'.$this->logStyles[$type].'">'.$logLine."</div>\n";
                flush();
            }
        }
        if (!empty($this->logPrefix)) {
            if ($this->logType == 'file' || $this->logType == 'both') {
                if (!empty($this->logFileName)) {
                    if (!$this->_logFileHandle) {
                        $this->_logFileHandle = fopen($this->logFileName, 'a+');
                    }
                    if ($this->_logFileHandle !== null) {
                        fwrite($this->_logFileHandle, $logLine);
                    }
                }
            }
            if ($type == 'error') {
                if (!empty($this->errorLogFile)) {
                    if (!$this->_errorLogFileHandle) {
                        $this->_errorLogFileHandle = fopen($this->errorLogFile,
                            'a+');
                    }
                    if ($this->_errorLogFileHandle) {
                        fputs($this->_errorLogFileHandle, $logLine);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Přejmenuje soubor s logem.
     *
     * @param string $newLogFileName new log filename
     *
     * @return string
     */
    public function renameLogFile($newLogFileName)
    {
        $newLogFileName = dirname($this->logFileName).'/'.basename($newLogFileName);
        if (rename($this->logFileName, $newLogFileName)) {
            return realpath($newLogFileName);
        } else {
            return realpath($this->logFileName);
        }
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
            if (isset($_SERVER['HTTP_HOST'])) {
                $this->runType = 'web';
            } else {
                $this->runType = 'cgi';
            }

            return $this->runType;
        }
        if (($runType != 'web') || ($runType != 'cgi')) {
            return;
        } else {
            return $this->runType;
        }
    }

    /**
     * Zkontroluje stav adresáře a upozorní na případné nesnáze.
     *
     * @param string $directoryPath cesta k adresáři
     * @param bool   $isDir         detekovat existenci adresáře
     * @param bool   $isReadable    testovat čitelnost
     * @param bool   $isWritable    testovat zapisovatelnost
     * @param bool   $logToFile     povolí logování do souboru
     *
     * @return bool konečný výsledek testu
     */
    public static function testDirectory($directoryPath, $isDir = true,
                                         $isReadable = true, $isWritable = true,
                                         $logToFile = false)
    {
        $sanity = true;
        if ($isDir) {
            if (!is_dir($directoryPath)) {
                echo $directoryPath._(' not an folder. Current directory:').' '.getcwd();
                if ($logToFile) {
                    \Ease\Shared::logger()->addToLog('TestDirectory',
                        $directoryPath._(' not an folder. Current directory:').' '.getcwd());
                }
                $sanity = false;
            }
        }
        if ($isReadable) {
            if (!is_readable($directoryPath)) {
                echo $directoryPath._(' not an readable folder. Current directory:').' '.getcwd();
                if ($logToFile) {
                    \Ease\Shared::logger()->addToLog('TestDirectory',
                        $directoryPath._(' not an readable folder. Current directory:').' '.getcwd());
                }
                $sanity = false;
            }
        }
        if ($isWritable) {
            if (!is_writable($directoryPath)) {
                echo $directoryPath._(' not an writeable folder. Current directory:').' '.getcwd();
                if ($logToFile) {
                    \Ease\Shared::logger()->addToLog('TestDirectory',
                        $directoryPath._(' not an writeable folder. Current directory:').' '.getcwd());
                }

                $sanity = false;
            }
        }

        return $sanity;
    }

    /**
     * Oznamuje chybovou událost.
     *
     * @param string $caller     název volající funkce, nebo objektu
     * @param string $message    zpráva
     * @param mixed  $objectData data k zaznamenání
     */
    public function error($caller, $message, $objectData = null)
    {
        if ($this->errorLogFile) {
            $logFileHandle = @fopen($this->errorLogFile, 'a+');
            if ($logFileHandle) {
                if ($this->easeShared->runType == 'web') {
                    fputs($logFileHandle,
                        \Ease\Brick::printPreBasic($_SERVER)."\n #End of Server enviroment  <<<<<<<<<<<<<<<<<<<<<<<<<<< # \n\n");
                } else {
                    fputs($logFileHandle,
                        \Ease\Brick::printPreBasic($_ENV)."\n #End of CLI enviroment  <<<<<<<<<<<<<<<<<<<<<<<<<<< # \n\n");
                }
                if (isset($_POST) && count($_POST)) {
                    fputs($logFileHandle,
                        \Ease\Brick::printPreBasic($_POST)."\n #End of _POST  <<<<<<<<<<<<<<<<<<<<<<<<<<< # \n\n");
                }
                if (isset($_GET) && count($_GET)) {
                    fputs($logFileHandle,
                        \Ease\Brick::printPreBasic($_GET)."\n #End of _GET enviroment  <<<<<<<<<<<<<<<<<<<<<<<<<<< # \n\n");
                }
                if ($objectData) {
                    fputs($logFileHandle,
                        \Ease\Brick::printPreBasic($objectData)."\n #End of ObjectData >>>>>>>>>>>>>>>>>>>>>>>>>>>># \n\n");
                }
                fclose($logFileHandle);
            } else {
                $this->addToLog('Error: Couldn\'t open the '.realpath($this->errorLogFile).' error log file',
                    'error');
            }
        }
        $this->addToLog($caller, $message, 'error');
    }

    /**
     * Uzavře chybové soubory.
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
     * Vrací styl logování.
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
