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
     * Output logline to syslog/messages by its type
     *
     * @param string $type    message type 'error' or anything else
     * @param string $logLine message to output
     */
    public function output($type, $logLine)
    {
        switch ($type) {
            case 'error':
                syslog(LOG_ERR, $this->finalizeMessage($logLine));
                break;
            default:
                syslog(LOG_INFO, $this->finalizeMessage($logLine));
                break;
        }
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
