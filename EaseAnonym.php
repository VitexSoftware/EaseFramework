<?php

/**
 * Objekty uživatelů
 * 
 * PHP Version 5
 * 
 * @package   EaseFrameWork
 * @author    Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright 2009-2011 Vitex@hippy.cz (G) 
 */
require_once 'EaseBase.php';

/**
 * Objekt Anonymního uživatele
 * 
 * @package EaseFrameWork
 * @author  Vitex <vitex@hippy.cz>
 */
class EaseAnonym extends EaseBrick
{

    /**
     * Druh uživatele
     * @var string
     */
    public $Type = 'anonymous';

    /**
     * Anonymní uživatel má vždy ID null
     * @var null
     */
    public $UserID = null;

    /**
     * Nakupni kosik anonymniho zakaznika
     * @var EaseCart
     */
    public $ShoppingCart = null;
    public $Language = 'cs';

    /**
     * Registr vlastnosti uzivatele
     * @var array
     */
    public $ValuesToKeep = array();

    /**
     * Anonym není v žádné obchodní skupině
     * @var array
     */
    public $BusinessGroups = array();

    /**
     *
     * @var type 
     */
    public $CustomerAddress = null;

    /**
     * Indikátor přihlášení
     * @var boolean 
     */
    public $Logged = false;

    /**
     * Nastavení jména objektu uživatele
     * 
     * @param string $ObjectName vynucené jméno objektu
     * 
     * @return string 
     */
    function setObjectName($ObjectName = null)
    {
        if (!$ObjectName && isset($_SERVER['REMOTE_ADDR'])) {
            if (isset($_SERVER['REMOTE_USER'])) {
                $Identity = $_SERVER['REMOTE_ADDR'] . ' [' . $_SERVER['REMOTE_USER'] . ']';
            } else {
                $Identity = $_SERVER['REMOTE_ADDR'];
            }
            return parent::setObjectName(get_class($this) . '@' . $Identity);
        } else {
            return parent::setObjectName($ObjectName);
        }
    }

    /**
     * Anonym má level
     * 
     * @return int
     */
    function getUserLevel()
    {
        return -1;
    }

    /**
     * Anonym nema ID
     */
    function getUserID()
    {
        return null;
    }

    /**
     * Anonym nema IDS
     */
    function getUserIDS()
    {
        return null;
    }

    /**
     * Anonym nemá login
     * 
     * @return null
     */
    function getUserLogin()
    {
        return null;
    }

    /**
     * Anonym nemůže být přihlášený
     * 
     * @return bool FALSE
     */
    function isLogged()
    {
        return $this->Logged;
    }

    /**
     * Anonym nemá nastavení
     * 
     * @param string $SettingName jméno klíče nastavení
     * 
     * @return null
     */
    function getSettingValue($SettingName = null)
    {
        return null;
    }

    /**
     * Anonym nemá mail
     * 
     * @return null
     */
    function getUserEmail()
    {
        return null;
    }

    /**
     * Vrací cenu pro anonymního uživatele
     * 
     * @param float $ProductPriceAnon anonymní cena
     * @param int   $ProductsID       ID produktu v shopu
     * @param int   $ProductsPohodaID ID produktu v PohodaSQL
     * 
     * @todo Přesunout do EaseCustomer
     * 
     * @return float 
     */
    function getUserPrice($ProductPriceAnon, $ProductsID, $ProductsPohodaID)
    {
        return $ProductPriceAnon;
    }

    /**
     * Maketa oprávnění
     * 
     * @param string $PermKeyword klíčové slovo oprávnění
     * 
     * @return null 
     */
    function getPermission($PermKeyword = null)
    {
        return null;
    }

    /**
     * Pouze maketa
     * 
     * @return bool
     */
    function logout()
    {
        $this->UserID = null;
        return true;
    }

    /**
     * Otestuje kvalitu hesla
     * 
     * @param string $Password heslo k otestování
     * 
     * @return boolean 
     * @todo Dořešit Cracklib ...
     */
    function passwordCrackCheck($Password)
    {
        return EaseCustomer::PasswordCrackCheck($Password);
    }

}

?>