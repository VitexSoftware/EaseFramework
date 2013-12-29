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
require_once 'EaseAnonym.php';

/**
 * Třída uživatele
 *
 * @package EaseFrameWork
 * @author  Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseUser extends EaseAnonym
{

    /**
     * Pracujem s tabulkou user
     * @var string
     */
    public $myTable = 'user';

    /**
     * Klíčový sloupeček tabulky
     * @var string
     */
    public $myKeyColumn = 'id';

    /**
     * Sloupecek obsahujici datum vložení záznamu uživatele do shopu
     * @var string
     */
    public $MyCreateColumn = 'DatCreate';

    /**
     * Sloupecek obsahujici datum poslení modifikace záznamu uživatele do shopu
     * @var string
     */
    public $MyLastModifiedColumn = 'DatSave';

    /**
     * Pole práv uživatele
     * @var array
     */
    public $Permissions = null;

    /**
     * Nactena prava uzivatele
     * @var array
     */
    public $PermissionsInactive = null;  //Prava na ktera jiz uzivatel z duvodu nizkeho levelu nedosahne
    /**
     * ID Vlastnika uzivatele
     * @var int unsigned
     */
    public $Parent = null;

    /**
     * ID prave nacteneho uzivatele
     * @var int unsigned
     */
    public $UserID = null;

    /**
     * Přihlašovací jméno uživatele
     * @var string
     */
    public $UserLogin = null;

    /**
     * Seznam ID podrizenych uzivatelu
     * @var array
     */
    public $SlaveUsers = null;

    /**
     * Level uživatele
     * @var int unsigned
     */
    public $UserLevel = null;

    /**
     * Registr vlastnosti uzivatele
     * @var array
     */
    public $ValuesToKeep = array();

    /**
     * Pole uživatelských nastavení
     * @var array
     */
    public $Settings = array();

    /**
     * Sloupeček s loginem
     * @var string
     */
    public $LoginColumn = 'login';

    /**
     * Sloupeček s heslem
     * @var string
     */
    public $PasswordColumn = 'password';

    /**
     * Sloupecek pro docasne zablokovani uctu
     * @var type
     */
    public $DisableColumn = null;

    /**
     * Column for user mail
     * @var string
     */
    public $MailColumn = 'email';

    /**
     * Sloupeček obsahující serializované rozšířené informace
     * @var string
     */
    public $settingsColumn = null;

    /**
     * Měna uživatele
     *
     * @var string
     */
    public $Currency = 'Kč';

    /**
     * Objekt uživatele aplikace
     *
     * @param int|string $UserID ID nebo Login uživatele jenž se má načíst při
     *        inicializaci třídy
     */
    public function __construct($UserID = null)
    {
        parent::__construct();
        if (!is_null($UserID)) {
            if (is_int($UserID)) {
                $this->loadFromMySQL($UserID);
            } else {
                if (isset($this->LoginColumn)) {
                    $this->setmyKeyColumn($this->LoginColumn);
                    $this->loadFromMySQL($UserID);
                    $this->resetObjectIdentity();
                }
            }
        }
        $this->setObjectName();
    }

    /**
     * Give you user name
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->getDataValue($this->LoginColumn);
    }

    /**
     * Retrun user's mail address
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->getDataValue($this->MailColumn);
    }

    /**
     * Vykreslí GrAvatara uživatele
     */
    public function draw()
    {
        echo '<img class="avatar" src="' . $this->getIcon() . '">';
    }

    /**
     * Vrací odkaz na url ikony
     *
     * @return string url ikony
     */
    public function getIcon()
    {
        $Email = $this->getUserEmail();
        if ($Email) {
            return self::getGravatar($Email, 800, 'mm', 'g', true, array('title' => $this->getUserName(), 'class' => 'gravatar_icon'));
        } else {
            return null;
        }
    }

    /**
     * Pokusí se o přihlášení
     *
     * @param array $FormData pole dat z přihlaš. formuláře např. $_REQUEST
     *
     * @return bool
     */
    public function tryToLogin($FormData)
    {
        if (!count($FormData)) {
            return null;
        }
        $Login = $this->easeAddSlashes($FormData[$this->LoginColumn]);
        $Password = $this->easeAddSlashes($FormData[$this->PasswordColumn]);
        if (!$Login) {
            $this->addStatusMessage(_('chybí login'), 'error');

            return null;
        }
        if (!$Password) {
            $this->addStatusMessage(_('chybí heslo'), 'error');

            return null;
        }
        $this->setObjectIdentity(array('myKeyColumn' => $this->LoginColumn));
        $this->loadFromMySQL($Login);
        $this->setObjectName();
        $this->resetObjectIdentity(array('ObjectName'));
        if ($this->passwordValidation($Password, $this->getDataValue($this->PasswordColumn))) {
            if ($this->isAccountEnabled()) {
                return $this->loginSuccess();
            } else {
                //$this->AReset(); MEGATODO - CO TO JE?
                $this->UserID = null;

                return false;
            }
        } else {
            $this->UserID = null;
            if (count($this->getData())) {
                $this->addStatusMessage(_('neplatné heslo'), 'error');
            } else {
                $this->addStatusMessage(sprintf(_('uživatel %s neexistuje'), $Login, 'error'));
            }
            $this->dataReset();

            return false;
        }
    }

    /**
     * Je učet povolen ?
     *
     * @return boolean
     */
    public function isAccountEnabled()
    {
        if (is_null($this->DisableColumn)) {
            return true;
        }
        if ($this->getDataValue($this->DisableColumn)) {
            $this->addStatusMessage(_('přihlášení zakázáno administrátorem'), 'warning');

            return false;
        }

        return true;
    }

    /**
     * Akce provedené po úspěšném přihlášení
     * pokud tam jeste neexistuje zaznam, vytvori se novy
     */
    public function loginSuccess()
    {
        $this->UserID = (int) $this->getMyKey();
        $this->UserLogin = $this->getDataValue($this->LoginColumn);
        $this->Logged = true;
        $this->addStatusMessage( sprintf( _('Přihlášení %s proběhlo bez problémů'),  $this->UserLogin), 'success');

        return true;
    }

    /**
     * Načte nastavení uživatele
     *
     * @param array $Settings Serializované pole nastavení
     *
     * @return boolean uspěch
     */
    public function loadSettings($Settings = null)
    {
        if (is_null($Settings)) {
            $Settings = $this->getDataValue($this->settingsColumn);
        }
        if (!is_null($Settings)) {
            $this->Settings = unserialize($Settings);

            return true;
        }

        return false;
    }

    /**
     * Vrací všechna nastavení uživatele
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->Settings;
    }

    /**
     * Ověření hesla
     *
     * @param string $PlainPassword     heslo v nešifrované podobě
     * @param string $EncryptedPassword šifrovné heslo
     *
     * @return bool
     */
    public function passwordValidation($PlainPassword, $EncryptedPassword)
    {
        if ($PlainPassword && $EncryptedPassword) {
            $PasswordStack = explode(':', $EncryptedPassword);
            if (sizeof($PasswordStack) != 2) {
                return false;
            }
            if (md5($PasswordStack[1] . $PlainPassword) == $PasswordStack[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Zašifruje heslo
     *
     * @param string $PlainTextPassword nešifrované heslo (plaintext)
     *
     * @return string Encrypted password
     */
    public function encryptPassword($PlainTextPassword)
    {
        $EncryptedPassword = '';
        for ($i = 0; $i < 10; $i++) {
            $EncryptedPassword .= $this->RandomNumber();
        }
        $PasswordSalt = substr(md5($EncryptedPassword), 0, 2);
        $EncryptedPassword = md5($PasswordSalt . $PlainTextPassword) . ':' . $PasswordSalt;

        return $EncryptedPassword;
    }

    /**
     * Změní uživateli uložené heslo
     *
     * @param string $NewPassword nové heslo
     * @param int    $UserID      id uživatele
     *
     * @return string password hash
     */
    public function passwordChange($NewPassword, $UserID = null)
    {
        if (!$UserID) {
            $UserID = $this->getUserID();
        }
        if (!$UserID) {
            $this->error('PasswordChange: UserID unset');

            return null;
        }
        $Hash = $this->encryptPassword($NewPassword);
        $this->myDbLink->exeQuery('UPDATE ' . $this->myTable . ' SET ' . $this->PasswordColumn . '=\'' . $Hash . '\' WHERE ' . $this->myKeyColumn . '=' . $UserID);
        $this->addToLog('PasswordChange: ' . $this->getDataValue($this->LoginColumn) . '@' . $UserID . '#' . $this->getDataValue($this->MyIDSColumn) . ' ' . $Hash);
        if ($UserID == $this->getUserID()) {
            $this->Data[$this->PasswordColumn] = $Hash;
        }

        return $Hash;
    }

    /**
     * Otestuje heslo oproti cracklib
     *
     * @param string $Password testované heslo
     *
     * @return boolen
     */
    public function passwordCrackCheck($Password)
    {
        if (!is_file('/usr/share/dict/cracklib-words')) {
            return true;
        }
        if (!function_exists('crack_opendict')) {
            $this->error('PECL Crack is not installed');

            return true;
        }
        $Dictonary = crack_opendict('/usr/share/dict/cracklib-words');
        $check = crack_check($Dictonary, $Password);
        $this->addStatusMessage(crack_getlastmessage());
        crack_closedict($Dictonary);

        return $check;
    }

    /**
     * Nastaví level uživatele
     *
     * @param int $UserLevel uživatelská uroven
     *
     * @todo Přesunout do EaseCustomer
     */
    public function setUserLevel($UserLevel)
    {
        $this->UserLevel = intval($UserLevel);
    }

    /**
     * Vraci ID přihlášeného uživatele
     *
     * @return int ID uživatele
     */
    public function getUserID()
    {
        if (isset($this->UserID)) {
            return (int) $this->UserID;
        }

        return (int) $this->getMyKey();
    }

    /**
     * Vrací login uživatele
     *
     * @return string
     */
    public function getUserLogin()
    {
        if (!isset($this->UserLogin)) {
            return $this->getDataValue($this->LoginColumn);
        }

        return $this->UserLogin;
    }

    /**
     * Vrací hodnotu uživatelského oprávnění
     *
     * @param string $PermKeyword klíčové slovo oprávnění
     *
     * @return mixed
     */
    public function getPermission($PermKeyword = null)
    {
        if (isset($this->Permissions[$PermKeyword])) {
            return $this->Permissions[$PermKeyword];
        } else {
            return null;
        }
    }

    /**
     * Provede odhlášení uživatele
     */
    public function logout()
    {
        $this->Logged = false;
        $this->addStatusMessage(_('Odhlášení proběhlo uspěšně'), 'success');

        return true;
    }

    /**
     * Vrací hodnotu nastavení
     *
     * @param string $SettingName jméno nastavení
     *
     * @return mixed
     */
    public function getSettingValue($SettingName = null)
    {
        if (isset($this->Settings[$SettingName])) {
            return $this->Settings[$SettingName];
        } else {
            return null;
        }
    }

    /**
     * Nastavuje nastavení
     *
     * @param array $Settings asociativní pole nastavení
     */
    public function setSettings($Settings)
    {
        $this->Settings = array_merge($this->Settings, $Settings);
    }

    /**
     * Nastaví položku nastavení
     *
     * @param string $SettingName  klíčové slovo pro nastavení
     * @param mixed  $SettingValue hodnota nastavení
     */
    public function setSettingValue($SettingName, $SettingValue)
    {
        $this->Settings[$SettingName] = $SettingValue;
    }

    /**
     * Načte oprávnění
     *
     * @return mixed
     */
    public function loadPermissions()
    {
        return null;
    }

    /**
     * Vrací jméno objektu uživatele
     *
     * @return string
     */
    public function getName()
    {
        return $this->getObjectName();
    }

    /**
     * Uloží pole dat a serializovaná nastavení do MySQL.
     * Pokud je $SearchForID 0 updatuje pokud ze nastaven  myKeyColumn
     *
     * @param array $Data        asociativní pole dat
     * @param bool  $SearchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToMySQL($Data = null, $SearchForID = false)
    {
        if (is_null($Data)) {
            if (array_key_exists('MySQL', $this->Data)) {
                $Data = $this->getData('MySQL');
            } else {
                $Data = $this->getData();
            }
        }
        if (!is_null($this->settingsColumn)) {
            $Data[$this->settingsColumn] = serialize($this->Settings);
        }

        return parent::saveToMySQL($Data, $SearchForID);
    }

    /**
     * Načte z MySQL data k aktuálnímu $ItemID a případně aplikuje nastavení
     *
     * @param int     $ItemID     id záznamu k načtení
     * @param string  $DataPrefix prefix pro rozlišení sady dat
     * @param boolean $Multiplete nevarovat v případě vícenásobného výsledku
     *
     * @return array Results
     */
    public function loadFromMySQL($ItemID = null, $DataPrefix = null, $Multiplete = false)
    {
        $Result = parent::loadFromMySQL($ItemID, $DataPrefix, $Multiplete);
        if (!is_null($this->settingsColumn) && !is_null($Result)) {
            $this->loadSettings();
        }

        return $Result;
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     *
     * @param string $email     The email address
     * @param string $Size      Size in pixels, defaults to 80px [ 1 - 512 ]
     * @param string $Default   [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $MaxRating Maximum rating (inclusive) [ g | pg | r | x ]
     *
     * @return String containing either just a URL or a complete image tag
     *
     * @source http://gravatar.com/site/implement/images/php/
     */
    public static function getGravatar($email, $Size = 80, $Default = 'mm', $MaxRating = 'g')
    {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$Size&d=$Default&r=$MaxRating";

        return $url;
    }

}

/**
 * Objekt zákazníka umí navíce od běžného uživatele, počítaní cen, nákupní košík
 * a obchodní skupiny
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseCustomer extends EaseUser
{
    /**
     * Pracujem s tabulkou user
     * @var string
     */
    public $myTable = 'customer';

    /**
     * Odkaz na adresu
     * @var type
     */
    public $customerDelivAddr = null;

    /**
     * Vrací (základní) cenu anonymního zákazníka s měnou
     *
     * @param float $ProductPriceAnon anonymní cena
     * @param int   $ProductsID       unsigned id produktu v Shopu
     * @param int   $ProductsPohodaID id produktu z PohodaSQL
     *
     * @return string
     */
    public function showUserPrice($ProductPriceAnon, $ProductsID = null, $ProductsPohodaID = null)
    {
        return $this->formatPrice($ProductPriceAnon);
    }

    /**
     * Vrací level uživatele
     *
     * @return int
     */
    public function getUserLevel()
    {
        return intval($this->UserLevel);
    }

}
