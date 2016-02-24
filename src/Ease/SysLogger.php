<?php

/**
 * Třída pro logování
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2015 Vitex@hippy.cz (G)
 */

namespace Ease;

/**
 * Třída pro logování
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
class SysLogger extends Atom
{

    /**
     * Předvolená metoda logování
     *
     * @var string
     */
    public $logType = 'syslog';

    /**
     * úroveň logování
     *
     * @var string - silent,debug
     */
    public $logLevel = 'debug';

    /**
     * Hodnoty pro obarvování logu
     *
     * @var array
     */
    public $logStyles = [
        'notice' => 'color: black;',
        'success' => 'color: #2C5F23;',
        'message' => 'color: #2C5F23;',
        'warning' => 'color: #AB250E;',
        'error' => 'color: red;',
        'debug' => 'font-style: italic;',
        'report' => 'font-wight: bold;',
        'info' => 'color: blue;'
    ];

    /**
     * Odkaz na vlastnící objekt
     *
     * @var Sand ||
     */
    public $parentObject = null;

    /**
     * Ukládat Zprávy do pole;
     *
     * @var boolean
     */
    private $storeMessages = false;

    /**
     * Pole uložených zpráv
     *
     * @var array
     */
    private $storedMessages = [];

    /**
     * ID naposledy ulozene zpravy
     *
     * @var int unsigned
     */
    private $messageID = 0;

    /**
     * Obecné konfigurace frameworku
     *
     * @var Shared
     */
    public $easeShared = null;

    /**
     * Saves obejct instace (singleton...)
     */
    private static $_instance = null;

    /**
     * Handle to logger
     *
     * @var resource
     */
    public $logger = null;

    /**
     * Logovací třída
     *
     * @param string $
     */
    public function __construct($logName = null)
    {
        if (!is_null($logName)) {
            $this->logger = openlog($logName, LOG_NDELAY, LOG_USER);
        }
        //        $this->easeShared = Shared::singleton();
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
            $class = __CLASS__;
            if (defined('LOG_NAME')) {
                self::$_instance = new $class(constant('LOG_NAME'));
            } else {
                self::$_instance = new $class('EaseFramework');
            }
        }

        return self::$_instance;
    }

    /**
     * Povolí nebo zakáže ukládání zpráv
     *
     * @param type $check
     */
    public function setStoreMessages($check)
    {
        $this->storeMessages = $check;
        if (is_bool($check)) {
            $this->resetStoredMessages();
        }
    }

    /**
     * Resetne pole uložených zpráv
     */
    public function resetStoredMessages()
    {
        $this->storedMessages = [];
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
     * @param string $Caller  název volajícího objektu
     * @param string $message zpráva
     * @param string $type    typ zprávy (success|info|error|warning|*)
     *
     * @return bool byl report zapsán ?
     */
    public function addToLog($Caller, $message, $type = 'message')
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

        $logLine = ' ~' . $Caller . '~ ' . str_replace(['notice', 'message', 'debug', 'report', 'error', 'warning', 'success', 'info', 'mail'], ['**', '##', '@@', '::'], $type) . ' ' . $message . "\n";
        if (!isset($this->logStyles[$type])) {
            $type = 'notice';
        }

        switch ($type) {
            case 'error':
                syslog(LOG_ERR, $logLine);
                break;
            default:
                syslog(LOG_INFO, $logLine);
                break;
        }


        return true;
    }

    /**
     * Oznamuje chybovou událost
     *
     * @param string $caller     název volající funkce, nebo objektu
     * @param string $message    zpráva
     * @param mixed  $objectData data k zaznamenání
     */
    public function error($caller, $message, $objectData = null)
    {
        if (!is_null($objectData)) {
            $message .= print_r($objectData, true);
        }
        $this->addToLog($caller, $message, 'error');
    }

    /**
     * Uzavře chybové soubory
     */
    public function __destruct()
    {
        if ($this->logger) {
            closelog();
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

    /**
     * Flush Messages
     *
     * @param string $caller
     */
    public function  flush($caller = null)
    {
        if (count($this->statusMessages)) {
            foreach ($this->statusMessages as $type => $messages) {
                foreach ($messages as $message) {
                    $this->addToLog($caller, $message, $type);
                }
            }
            $this->cleanMessages();
        }
    }

}
