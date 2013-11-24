<?php

/**
 * Základní pojící element všech objektů v EaseFrameWorku. Jeho hlavní schopnost je:
 * Pojímat do sebe zprávy.  
 * 
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G) 
 */

class EaseAtom
{

    /**
     * Udržuje v sobě jméno objektu.
     * @var string
     */
    public $ObjectName = 'EaseSand';

    /**
     * Flag debugovacího režimu
     * @var bool
     */
    public $Debug = false;

    /**
     * Pole informaci urcenych k logovani inebo zobrazovanych uzivateli
     * @var array
     */
    public $StatusMessages = array();

    /**
     * Pocet uchovavanych zprav
     * @var int 
     */
    public $MessageCount = 0;

    /**
     * Vrací jméno objektu
     * 
     * @return string
     */
    function getObjectName()
    {
        return get_class();
    }

    /**
     * Přidá zprávu do zásobníku pro zobrazení uživateli inbo do logu
     * 
     * @param string $Message text zpravy
     * @param string $Type    fronta
     */
    public function addStatusMessage($Message, $Type = 'info')
    {
        $this->MessageCount++;
        $this->StatusMessages[$Type][$this->MessageCount] = $Message;
    }

    /**
     * Přidá zprávy z pole uživateli do zásobníku
     * 
     * @param array $StatusMessages pole zpráv
     * 
     * @return int Počet zpráv přidaných do fronty
     */
    public function addStatusMessages($StatusMessages)
    {
        if (is_array($StatusMessages) && count($StatusMessages)) {
            $AllMessages = array();
            foreach ($StatusMessages as $Quee => $Messages) {
                foreach ($Messages as $MesgID => $Message) {
                    $AllMessages[$MesgID][$Quee] = $Message;
                }
            }
            ksort($AllMessages);
            foreach ($AllMessages as $Message) {
                $Quee = key($Message);
                $this->addStatusMessage(reset($Message), $Quee, false, false);
            }
            return count($StatusMessages);
        }
        return null;
    }

    /**
     * Vymaže zprávy
     */
    public function cleanMessages()
    {
        $this->MessageCount = 0;
        $this->StatusMessages = array();
    }

    /**
     * Předá zprávy
     * 
     * @param boolean $Clean smazat originalni data ?
     * 
     * @return array
     */
    public function getStatusMessages($Clean = false)
    {
        if ($Clean) {
            $StatusMessages = $this->StatusMessages;
            $this->cleanMessages();
            return $StatusMessages;
        } else {
            return $this->StatusMessages;
        }
    }

    /**
     * Prevezme si zpravy z vnějšího zdroje
     * 
     * @param array $StatusMessages pole zpráv např. $OUser->StatusMessages
     */
    function takeStatusMessages($StatusMessages)
    {
        if (is_object($StatusMessages) && isset($StatusMessages->StatusMessages)) {
            return $this->addStatusMessages($StatusMessages->StatusMessages);
        } else {
            return $this->addStatusMessages($StatusMessages);
        }
    }

    /**
     * Returns PATH modified for current operating system
     * 
     * @param string $Path
     * 
     * @return string 
     */
    public static function sysFilename($Path)
    {
        $Path = str_replace('//', '/', $Path);
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $Path = str_replace('/', '\\', $Path);
        } else {
            $Path = str_replace('\\', '/', $Path);
        }
        return $Path;
    }

}

?>