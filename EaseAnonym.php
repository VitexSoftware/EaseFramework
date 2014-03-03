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
require_once 'EaseBrick.php';

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
    public $customerAddress = null;

    /**
     * Indikátor přihlášení
     * @var boolean
     */
    public $logged = false;

    /**
     * Nastavení jména objektu uživatele
     *
     * @param string $ObjectName vynucené jméno objektu
     *
     * @return string
     */
    public function setObjectName($ObjectName = null)
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
    public function getUserLevel()
    {
        return -1;
    }

    /**
     * Anonym nema ID
     */
    public function getUserID()
    {
        return null;
    }

    /**
     * Anonym nema IDS
     */
    public function getUserIDS()
    {
        return null;
    }

    /**
     * Anonym nemá login
     *
     * @return null
     */
    public function getUserLogin()
    {
        return null;
    }

    /**
     * Anonym nemůže být přihlášený
     *
     * @return bool FALSE
     */
    public function isLogged()
    {
        return $this->logged;
    }

    /**
     * Anonym nemá nastavení
     *
     * @param string $SettingName jméno klíče nastavení
     *
     * @return null
     */
    public function getSettingValue($SettingName = null)
    {
        return null;
    }

    /**
     * Anonym nemá mail
     *
     * @return null
     */
    public function getUserEmail()
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
    public function getUserPrice($ProductPriceAnon, $ProductsID, $ProductsPohodaID)
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
    public function getPermission($PermKeyword = null)
    {
        return null;
    }

    /**
     * Pouze maketa
     *
     * @return bool
     */
    public function logout()
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
    public function passwordCrackCheck($Password)
    {
        return EaseCustomer::PasswordCrackCheck($Password);
    }

}
