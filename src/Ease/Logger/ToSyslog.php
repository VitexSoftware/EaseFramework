<?php
/**
 * Třída pro logování.
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
class ToSyslog extends ToStd implements Loggingable
{
    /**
     * Předvolená metoda logování.
     *
     * @var string
     */
    public $logType = 'syslog';

    /**
     * Saves obejct instace (singleton...).
     */
    private static $_instance = null;

    /**
     * Handle to logger.
     *
     * @var resource
     */
    public $logger = null;

    /**
     * Logovací třída.
     *
     * @param string $
     */
    public function __construct($logName = null)
    {
        if (!is_null($logName)) {
            $this->logger = openlog($logName, LOG_NDELAY, LOG_USER);
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

        $logLine = ' `'.$caller.'` '.str_replace(['notice', 'message', 'debug', 'report',
                'error', 'warning', 'success', 'info', 'mail',],
                ['**', '##', '@@', '::'], $type).' '.$message."\n";
        if (!isset($this->logStyles[$type])) {
            $type = 'notice';
        }

        switch ($type) {
            case 'error':
                syslog(LOG_ERR, $this->finalizeMessage($logLine));
                break;
            default:
                syslog(LOG_INFO, $this->finalizeMessage($logLine));
                break;
        }

        return true;
    }

    /**
     * Uzavře chybové soubory.
     */
    public function __destruct()
    {
        if ($this->logger) {
            closelog();
        }
    }
}