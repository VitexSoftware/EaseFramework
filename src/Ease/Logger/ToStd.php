<?php
/**
 * Log to stdout/stderr
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2016 Vitex@hippy.cz (G)
 */

namespace Ease\Logger;

/**
 * Log to syslog.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
class ToStd extends ToMemory
{
    /**
     * Předvolená metoda logování.
     *
     * @var string
     */
    public $logType = 'stdout';

    /**
     * úroveň logování.
     *
     * @var string - silent,debug
     */
    public $logLevel = 'debug';

    /**
     * Odkaz na vlastnící objekt.
     *
     * @var Sand ||
     */
    public $parentObject = null;

    /**
     * Pole uložených zpráv.
     *
     * @var array
     */
    public $statusMessages = [];

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
     * List of allready flushed messages.
     *
     * @var array
     */
    public $flushed = [];

    /**
     * Saves obejct instace (singleton...).
     */
    private static $_instance = null;

    /**
     * Log Name
     *
     * @var resource
     */
    public $logName = null;

    /**
     * Logovací třída.
     *
     * @param string $
     */
    public function __construct($logName = null)
    {
        $this->logName = $logName;
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
            if (defined('EASE_APPNAME')) {
                self::$_instance = new $class(constant('EASE_APPNAME'));
            } else {
                self::$_instance = new $class('EaseFramework');
            }
        }

        return self::$_instance;
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

        $user = \Ease\Shared::user();
        if( get_class($user) !== 'Ease\\Anonym' ) {
            if( method_exists($user, 'getUserName')){
                $person =  $user->getUserName();
            } else {
                $person = $user->getObjectName();
            }
            $caller = $person.' '.$caller;
        } 

        $logLine = ' `'.$caller.'` '.str_replace(['notice', 'message', 'debug', 'report',
                'error', 'warning', 'success', 'info', 'mail',],
                ['**', '##', '@@', '::'], $type).' '.$message."\n";
        if (!isset($this->logStyles[$type])) {
            $type = 'notice';
        }

        $this->output($type, $logLine);

        return true;
    }

    /**
     * Output logline to stderr/stdout by its type
     *
     * @param string $type    message type 'error' or anything else
     * @param string $logLine message to output
     */
    public function output($type, $logLine)
    {
        switch ($type) {
            case 'error':
                $stderr = fopen('php://stderr', 'w');
                fwrite($stderr, $this->logName.': '.$logLine);
                fclose($stderr);
                break;
            default:
                $stdout = fopen('php://stdout', 'w');
                fwrite($stdout, $this->logName.': '.$logLine);
                fclose($stdout);
                break;
        }
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
        if (!is_null($objectData)) {
            $message .= print_r($objectData, true);
        }
        $this->addToLog($caller, $message, 'error');
    }

    /**
     * Last message check/modify point before output
     *
     * @param string $messageRaw
     *
     * @return string ready to use message
     */
    public function finalizeMessage($messageRaw)
    {
        return trim($messageRaw).PHP_EOL;
    }

    /**
     * Flush Messages.
     *
     * @param string $caller
     *
     * @return int how many messages was flushed
     */
    public function flush($caller = null)
    {
        $flushed = 0;
        if (count($this->statusMessages)) {
            foreach ($this->statusMessages as $type => $messages) {
                foreach ($messages as $messageID => $message) {
                    if (!isset($this->flushed[$type][$messageID])) {
                        $this->addToLog($caller, $message, $type);
                        $this->flushed[$type][$messageID] = true;
                        ++$flushed;
                    }
                }
            }
        }

        return $flushed;
    }
}
