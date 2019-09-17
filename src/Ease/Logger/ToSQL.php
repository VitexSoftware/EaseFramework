<?php
/**
 * EasePHP FrameWork
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2018 Vitex@hippy.cz (G)
 */

namespace Ease\Logger;

/**
 * Logogger To SQL Class.
 *
 * 
  CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(255) NOT NULL COMMENT 'info|warning|error|..',
  `when` datetime NOT NULL COMMENT 'log time',
  `sender` varchar(255) NOT NULL COMMENT 'Message is Produced by',
  `message` varchar(255) NOT NULL COMMENT 'Logged message itself',
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8
 * 
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
class ToSQL extends ToStd implements Loggingable
{

    use \Ease\SQL\Orm;
    /**
     * Předvolená metoda logování.
     *
     * @var string
     */
    public $logType = 'sql';

    /**
     * Saves obejct instace (singleton...).
     */
    private static $_instance = null;

    /**
     * Logovací třída.
     *
     * @param string $logName table used to save logsu
     */
    public function __construct($logName = null)
    {
        $this->keyColumn = 'id';
        $this->takemyTable(empty($logName) ? 'log' : $logName );
    }

    /**
     * 
     * @return string
     */
    public function getKeyColumn()
    {
        return $this->keyColumn;
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
            $class           = __CLASS__;
            self::$_instance = new $class();
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

        $this->insertToSQL([
            'status' => $type,
            'when' => 'NOW()',
            'sender' => $caller,
            'message' => $message]);
    }
}
