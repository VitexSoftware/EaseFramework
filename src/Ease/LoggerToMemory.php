<?php
/**
 * Třída pro logování.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */

namespace Ease;

class LoggerToMemory extends Atom
{
    /**
     * Předvolená metoda logování.
     *
     * @var string
     */
    public $logType = 'memory';

    /**
     * Adresář do kterého se zapisují logy.
     *
     * @var string dirpath
     */
    public $logPrefix = null;

    /**
     * úroveň logování.
     *
     * @var string - silent,debug
     */
    public $logLevel = 'debug';

    /**
     * Hodnoty pro obarvování logu.
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
        'info' => 'color: blue;',
    ];

    /**
     * Odkaz na vlastnící objekt.
     *
     * @var EaseSand ||
     */
    public $parentObject = null;

    /**
     * Ukládat Zprávy do pole;.
     *
     * @var bool
     */
    private $storeMessages = false;

    /**
     * Pole uložených zpráv.
     *
     * @var array
     */
    private $storedMessages = [];

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
     * Keep log in memory Class.
     */
    public function __construct()
    {
        $this->easeShared = Shared::singleton();
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
            self::$_instance = new $class();
        }

        return self::$_instance;
    }

    /**
     * Povolí nebo zakáže ukládání zpráv.
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
     * Resetne pole uložených zpráv.
     */
    public function resetStoredMessages()
    {
        $this->storedMessages = [];
    }

    /**
     * Vrací pole uložených zpráv.
     *
     * @return array
     */
    public function getStoredMessages()
    {
        return $this->storedMessages;
    }

    /**
     * Zapise zapravu do logu.
     *
     * @param string $caller  název volajícího objektu
     * @param string $message zpráva
     * @param string $type    typ zprávy (success|info|error|warning|*)
     *
     * @return bool byl report zapsán ?
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
        if ($this->storeMessages) {
            $this->storedMessages[$type][$this->messageID] = $message;
        }

        $message = htmlspecialchars_decode(strip_tags(stripslashes($message)));

        $logLine = date(DATE_ATOM).' ('.$caller.') '.str_replace(['notice', 'message',
                'debug', 'report', 'error', 'warning', 'success', 'info', 'mail', ],
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

        return true;
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
        $this->addToLog($caller, $message, 'error');
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

    /**
     * Do Nothing.
     */
    public function flush()
    {
        //Hotfix
    }
}
