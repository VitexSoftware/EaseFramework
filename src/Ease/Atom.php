<?php
/**
 * Základní pojící element všech objektů v EaseFrameWorku. Jeho hlavní schopnost je:
 * Pojímat do sebe zprávy.
 * 
 * @category Common
 * 
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2019 Vitex@hippy.cz (G)
 * @license http://URL GPL-2
 * 
 * PHP 7
 */

namespace Ease;

/**
 * Basic Class of EasePHP
 * 
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class Atom
{
    /**
     * Version of EasePHP Framework
     *
     * @var string
     */
    static public $frameworkVersion = '1.16';

    /**
     * Udržuje v sobě jméno objektu.
     *
     * @var string
     */
    public $objectName = 'EaseSand';

    /**
     * Flag debugovacího režimu.
     *
     * @var bool
     */
    public $debug = false;

    /**
     * Pole informaci urcenych k logovani inebo zobrazovanych uzivateli.
     *
     * @var array
     */
    public $statusMessages = [];

    /**
     * Pocet uchovavanych zprav.
     *
     * @var int
     */
    public $messageCount = 0;

    /**
     * Vrací jméno objektu.
     *
     * @return string
     */
    public function getObjectName()
    {
        return get_class();
    }

    /**
     * Add message to stack to show or write to file
     * Přidá zprávu do zásobníku pro zobrazení uživateli inbo do logu.
     *
     * @param string $message text zpravy
     * @param string $type    fronta
     */
    public function addStatusMessage($message, $type = 'info')
    {
        ++$this->messageCount;
        $this->statusMessages[$type][$this->messageCount] = $message;
    }

    /**
     * Přidá zprávy z pole uživateli do zásobníku.
     *
     * @param array $statusMessages pole zpráv
     *
     * @return int Počet zpráv přidaných do fronty
     */
    public function addStatusMessages($statusMessages)
    {
        if (is_array($statusMessages) && count($statusMessages)) {
            $allMessages = [];
            foreach ($statusMessages as $quee => $messages) {
                foreach ($messages as $mesgID => $message) {
                    $allMessages[$mesgID][$quee] = $message;
                }
            }
            ksort($allMessages);
            foreach ($allMessages as $message) {
                $quee = key($message);
                $this->addStatusMessage(reset($message), $quee);
            }

            return count($statusMessages);
        }

        return;
    }

    /**
     * Vymaže zprávy.
     */
    public function cleanMessages()
    {
        $this->messageCount   = 0;
        $this->statusMessages = [];
    }

    /**
     * Předá zprávy.
     *
     * @param bool $clean smazat originalni data ?
     *
     * @return array
     */
    public function getStatusMessages($clean = false)
    {
        if ($clean) {
            $statusMessages = $this->statusMessages;
            $this->cleanMessages();

            return $statusMessages;
        } else {
            return $this->statusMessages;
        }
    }

    /**
     * Prevezme si zpravy z vnějšího zdroje.
     *
     * @param array $statusMessages pole zpráv např. $OUser->StatusMessages
     */
    public function takeStatusMessages($statusMessages)
    {
        if (is_object($statusMessages) && isset($statusMessages->statusMessages)) {
            return $this->addStatusMessages($statusMessages->statusMessages);
        } else {
            return $this->addStatusMessages($statusMessages);
        }
    }

    /**
     * Returns PATH modified for current operating system.
     *
     * @param string $path
     *
     * @return string
     */
    public static function sysFilename($path)
    {
        return str_replace(['\\', '/'], constant('DIRECTORY_SEPARATOR'), $path);
    }

    /**
     * Magická funkce pro všechny potomky.
     *
     * @return string
     */
    public function __toString()
    {
        return '';
    }

    /**
     * Default Draw method.
     *
     * @return string
     */
    public function draw()
    {
        if (method_exists($this, '__toString')) {
            return $this->__toString();
        }
    }
}
