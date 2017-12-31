<?php
/**
 * Log to Windows Event Log.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2016 Vitex@hippy.cz (G)
 */

namespace Ease\Logger;

/**
 * Log to EventLog.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2017 Vitex@hippy.cz (G)
 */
class ToEventlog extends ToSyslog implements Loggingable
{
    /**
     * Předvolená metoda logování.
     *
     * @var string
     */
    public $logType = 'eventlog';

    /**
     * Saves obejct instace (singleton...).
     */
    private static $_instance = null;

    /**
     * Encode For Windows event Log
     *
     * @param string $messageRaw
     *
     * @return string ready to use message
     */
    public function finalizeMessage($messageRaw)
    {
        return iconv("UTF-8", "cp1251", $message);
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
        
}
