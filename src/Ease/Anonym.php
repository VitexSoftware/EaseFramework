<?php

/**
 * Objekt Anonymního uživatele
 *
 * PHP Version 5
 *
 * @package   EaseFrameWork
 * @author    Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright 2009-2011 Vitex@hippy.cz (G)
 */

namespace Ease;

class Anonym extends Brick
{

    /**
     * Druh uživatele
     *
     * @var string
     */
    public $type = 'anonymous';

    /**
     * Anonymní uživatel má vždy ID null
     *
     * @var null
     */
    public $userID = null;

    /**
     * Jazyk anonyma
     *
     * @var string
     */
    public $language = 'cs';

    /**
     * Registr vlastnosti uzivatele
     *
     * @var array
     */
    public $valuesToKeep = [];

    /**
     * Indikátor přihlášení
     *
     * @var boolean
     */
    public $logged = false;

    /**
     * Nastavení jména objektu uživatele
     *
     * @param string $objectName vynucené jméno objektu
     *
     * @return string
     */
    public function setObjectName($objectName = null)
    {
        if (!$objectName && isset($_SERVER['REMOTE_ADDR'])) {
            if (isset($_SERVER['REMOTE_USER'])) {
                $identity = $_SERVER['REMOTE_ADDR'] . ' [' . $_SERVER['REMOTE_USER'] . ']';
            } else {
                $identity = $_SERVER['REMOTE_ADDR'];
            }

            return parent::setObjectName(get_class($this) . '@' . $identity);
        } else {
            return parent::setObjectName($objectName);
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
     * @param string $settingName jméno klíče nastavení
     *
     * @return null
     */
    public function getSettingValue($settingName = null)
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
     * Maketa oprávnění
     *
     * @param string $permKeyword klíčové slovo oprávnění
     *
     * @return null
     */
    public function getPermission($permKeyword = null)
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
        $this->userID = null;

        return true;
    }

}
