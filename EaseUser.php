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
class EaseUser extends EaseAnonym {

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
    public $myCreateColumn = 'created_at';

    /**
     * Sloupecek obsahujici datum poslení modifikace záznamu uživatele do shopu
     * @var string
     */
    public $myLastModifiedColumn = null;

    /**
     * Pole práv uživatele
     * @var array
     */
    public $permissions = null;

    /**
     * Nactena prava uzivatele
     * @var array
     */
    public $permissionsInactive = null;  //Prava na ktera jiz uzivatel z duvodu nizkeho levelu nedosahne
    /**
     * Objekt nadřazeného uživatele
     * @var int unsigned
     */
    public $parent = null;

    /**
     * ID prave nacteneho uzivatele
     * @var int unsigned
     */
    public $userID = null;

    /**
     * Přihlašovací jméno uživatele
     * @var string
     */
    public $userLogin = null;

    /**
     * Seznam ID podrizenych uzivatelu
     * @var array
     */
    public $slaveUsers = null;

    /**
     * Level uživatele
     * @var int unsigned
     */
    public $userLevel = null;

    /**
     * Registr vlastnosti uzivatele
     * @var array
     */
    public $valuesToKeep = array();

    /**
     * Pole uživatelských nastavení
     * @var array
     */
    public $settings = array();

    /**
     * Sloupeček s loginem
     * @var string
     */
    public $loginColumn = 'login';

    /**
     * Sloupeček s heslem
     * @var string
     */
    public $passwordColumn = 'password';

    /**
     * Sloupecek pro docasne zablokovani uctu
     * @var type
     */
    public $disableColumn = null;

    /**
     * Column for user mail
     * @var string
     */
    public $mailColumn = 'email';

    /**
     * Sloupeček obsahující serializované rozšířené informace
     * @var string
     */
    public $settingsColumn = null;

    /**
     * Objekt uživatele aplikace
     *
     * @param int|string $userID ID nebo Login uživatele jenž se má načíst při
     *        inicializaci třídy
     */
    public function __construct($userID = null) {
        parent::__construct();
        if (!is_null($userID)) {
            if (is_int($userID)) {
                $this->loadFromMySQL($userID);
            } else {
                if (isset($this->loginColumn)) {
                    $this->setmyKeyColumn($this->loginColumn);
                    $this->loadFromMySQL($userID);
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
    public function getUserName() {
        return $this->getDataValue($this->loginColumn);
    }

    /**
     * Retrun user's mail address
     *
     * @return string
     */
    public function getUserEmail() {
        return $this->getDataValue($this->mailColumn);
    }

    /**
     * Vykreslí GrAvatara uživatele
     */
    public function draw() {
        echo '<img class="avatar" src="' . $this->getIcon() . '">';
    }

    /**
     * Vrací odkaz na url ikony
     *
     * @return string url ikony
     */
    public function getIcon() {
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
     * @param array $formData pole dat z přihlaš. formuláře např. $_REQUEST
     *
     * @return bool
     */
    public function tryToLogin($formData) {
        if (!count($formData)) {
            return null;
        }
        $login = $this->easeAddSlashes($formData[$this->loginColumn]);
        $password = $this->easeAddSlashes($formData[$this->passwordColumn]);
        if (!$login) {
            $this->addStatusMessage(_('chybí login'), 'error');

            return null;
        }
        if (!$password) {
            $this->addStatusMessage(_('chybí heslo'), 'error');

            return null;
        }
        $this->setObjectIdentity(array('myKeyColumn' => $this->loginColumn));
        if ($this->loadFromMySQL($login)) {
            $this->setObjectName();
            $this->resetObjectIdentity(array('ObjectName'));
            if ($this->passwordValidation($password, $this->getDataValue($this->passwordColumn))) {
                if ($this->isAccountEnabled()) {
                    return $this->loginSuccess();
                } else {
                    $this->userID = null;
                    return false;
                }
            } else {
                $this->userID = null;
                if (count($this->getData())) {
                    $this->addStatusMessage(_('neplatné heslo'), 'error');
                }
                $this->dataReset();

                return false;
            }
        } else {
            $this->addStatusMessage(sprintf(_('uživatel %s neexistuje'), $login, 'error'));
            return false;
        }
    }

    /**
     * Je učet povolen ?
     *
     * @return boolean
     */
    public function isAccountEnabled() {
        if (is_null($this->disableColumn)) {
            return true;
        }
        if ($this->getDataValue($this->disableColumn)) {
            $this->addStatusMessage(_('přihlášení zakázáno administrátorem'), 'warning');

            return false;
        }

        return true;
    }

    /**
     * Akce provedené po úspěšném přihlášení
     * pokud tam jeste neexistuje zaznam, vytvori se novy
     */
    public function loginSuccess() {
        $this->userID = (int) $this->getMyKey();
        $this->userLogin = $this->getDataValue($this->loginColumn);
        $this->logged = true;
        $this->addStatusMessage(sprintf(_('Přihlášení %s proběhlo bez problémů'), $this->userLogin), 'success');

        return true;
    }

    /**
     * Načte nastavení uživatele
     *
     * @param array $Settings Serializované pole nastavení
     *
     * @return boolean uspěch
     */
    public function loadSettings($Settings = null) {
        if (is_null($Settings)) {
            $Settings = $this->getDataValue($this->settingsColumn);
        }
        if (!is_null($Settings)) {
            $this->settings = unserialize($Settings);

            return true;
        }

        return false;
    }

    /**
     * Vrací všechna nastavení uživatele
     *
     * @return array
     */
    public function getSettings() {
        return $this->settings;
    }

    /**
     * Ověření hesla
     *
     * @param string $plainPassword     heslo v nešifrované podobě
     * @param string $encryptedPassword šifrovné heslo
     *
     * @return bool
     */
    public function passwordValidation($plainPassword, $encryptedPassword) {
        if ($plainPassword && $encryptedPassword) {
            $passwordStack = explode(':', $encryptedPassword);
            if (sizeof($passwordStack) != 2) {
                return false;
            }
            if (md5($passwordStack[1] . $plainPassword) == $passwordStack[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Zašifruje heslo
     *
     * @param string $plainTextPassword nešifrované heslo (plaintext)
     *
     * @return string Encrypted password
     */
    public function encryptPassword($plainTextPassword) {
        $encryptedPassword = '';
        for ($i = 0; $i < 10; $i++) {
            $encryptedPassword .= $this->randomNumber();
        }
        $passwordSalt = substr(md5($encryptedPassword), 0, 2);
        $encryptedPassword = md5($passwordSalt . $plainTextPassword) . ':' . $passwordSalt;

        return $encryptedPassword;
    }

    /**
     * Změní uživateli uložené heslo
     *
     * @param string $newPassword nové heslo
     * @param int    $userID      id uživatele
     *
     * @return string password hash
     */
    public function passwordChange($newPassword, $userID = null) {
        if (!$userID) {
            $userID = $this->getUserID();
        }
        if (!$userID) {
            return null;
        }
        $hash = $this->encryptPassword($newPassword);
        $this->myDbLink->exeQuery('UPDATE ' . $this->myTable . ' SET ' . $this->passwordColumn . '=\'' . $hash . '\' WHERE ' . $this->myKeyColumn . '=' . $userID);
        $this->addToLog('PasswordChange: ' . $this->getDataValue($this->loginColumn) . '@' . $userID . '#' . $this->getDataValue($this->myIDSColumn) . ' ' . $hash);
        if ($userID == $this->getUserID()) {
            $this->setDataValue($this->passwordColumn, $hash);
        }

        return $hash;
    }

    /**
     * Otestuje heslo oproti cracklib
     *
     * @param string $password testované heslo
     *
     * @return boolen
     */
    public function passwordCrackCheck($password) {
        if (!is_file('/usr/share/dict/cracklib-words')) {
            return true;
        }
        if (!function_exists('crack_opendict')) {
            $this->error('PECL Crack is not installed');
            return true;
        }
        $Dictonary = crack_opendict('/usr/share/dict/cracklib-words');
        $check = crack_check($Dictonary, $password);
        $this->addStatusMessage(crack_getlastmessage());
        crack_closedict($Dictonary);
        return $check;
    }

    /**
     * Nastaví level uživatele
     *
     * @param int $userLevel uživatelská uroven
     *
     * @todo Přesunout do EaseCustomer
     */
    public function setUserLevel($userLevel) {
        $this->userLevel = intval($userLevel);
    }

    /**
     * Vraci ID přihlášeného uživatele
     *
     * @return int ID uživatele
     */
    public function getUserID() {
        if (isset($this->userID)) {
            return (int) $this->userID;
        }

        return (int) $this->getMyKey();
    }

    /**
     * Vrací login uživatele
     *
     * @return string
     */
    public function getUserLogin() {
        if (!isset($this->userLogin)) {
            return $this->getDataValue($this->loginColumn);
        }

        return $this->userLogin;
    }

    /**
     * Nastavuje login uživatele
     *
     * @return string
     */
    public function setUserLogin($login) {
        $this->userLogin = $login;
        if (isset($this->loginColumn)) {
            return $this->setDataValue($this->loginColumn, $login);
        }

        return $this->userLogin;
    }

    /**
     * Vrací hodnotu uživatelského oprávnění
     *
     * @param string $permKeyword klíčové slovo oprávnění
     *
     * @return mixed
     */
    public function getPermission($permKeyword = null) {
        if (isset($this->permissions[$permKeyword])) {
            return $this->permissions[$permKeyword];
        } else {
            return null;
        }
    }

    /**
     * Provede odhlášení uživatele
     */
    public function logout() {
        $this->logged = false;
        $this->addStatusMessage(_('Odhlášení proběhlo uspěšně'), 'success');

        return true;
    }

    /**
     * Vrací hodnotu nastavení
     *
     * @param string $settingName jméno nastavení
     *
     * @return mixed
     */
    public function getSettingValue($settingName = null) {
        if (isset($this->settings[$settingName])) {
            return $this->settings[$settingName];
        } else {
            return null;
        }
    }

    /**
     * Nastavuje nastavení
     *
     * @param array $settings asociativní pole nastavení
     */
    public function setSettings($settings) {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * Nastaví položku nastavení
     *
     * @param string $settingName  klíčové slovo pro nastavení
     * @param mixed  $settingValue hodnota nastavení
     */
    public function setSettingValue($settingName, $settingValue) {
        $this->settings[$settingName] = $settingValue;
    }

    /**
     * Načte oprávnění
     *
     * @return mixed
     */
    public function loadPermissions() {
        return null;
    }

    /**
     * Vrací jméno objektu uživatele
     *
     * @return string
     */
    public function getName() {
        return $this->getObjectName();
    }

    /**
     * Uloží pole dat a serializovaná nastavení do MySQL.
     * Pokud je $SearchForID 0 updatuje pokud ze nastaven  myKeyColumn
     *
     * @param array $data        asociativní pole dat
     * @param bool  $searchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToMySQL($data = null, $searchForID = false) {
        if (is_null($data)) {
            if (array_key_exists('MySQL', $this->data)) {
                $data = $this->getData('MySQL');
            } else {
                $data = $this->getData();
            }
        }
        if (!is_null($this->settingsColumn)) {
            $data[$this->settingsColumn] = serialize($this->settings);
        }

        return parent::saveToMySQL($data, $searchForID);
    }

    /**
     * Načte z MySQL data k aktuálnímu $ItemID a případně aplikuje 
     * nastavení
     *
     * @param int     $itemID     id záznamu k načtení
     * @param string  $dataPrefix prefix pro rozlišení sady dat
     * @param boolean $multiplete nevarovat v případě vícenásobného 
     *                            výsledku
     *
     * @return array Results
     */
    public function loadFromMySQL(
    $itemID = null, $dataPrefix = null, $multiplete = false
    ) {
        $result = parent::loadFromMySQL($itemID, $dataPrefix, $multiplete);
        if (!is_null($this->settingsColumn) && !is_null($result)) {
            $this->loadSettings();
        }

        return $result;
    }

    /**
     * Get either a Gravatar URL or complete image tag for a specified email 
     * address.
     *
     * @param string $email     The email address
     * @param string $size      Size in pixels, defaults to 80px [ 1 - 512 ]
     * @param string $default   [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $maxRating Maximum rating (inclusive) [ g | pg | r | x ]
     *
     * @return String containing either just a URL or a complete image tag
     *
     * @source http://gravatar.com/site/implement/images/php/
     */
    public static function getGravatar(
    $email, $size = 80, $default = 'mm', $maxRating = 'g'
    ) {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$size&d=$default&r=$maxRating";

        return $url;
    }

    /**
     * Nastavení jména objektu uživatele
     *
     * @param string $objectName vynucené jméno objektu
     *
     * @return string
     */
    public function setObjectName($objectName = null) {
        if (!$objectName && isset($_SERVER['REMOTE_ADDR'])) {
            if (isset($_SERVER['REMOTE_USER'])) {
                $identity = $_SERVER['REMOTE_ADDR'] . ' [' . $_SERVER['REMOTE_USER'] . ']';
            } else {
                $identity = $_SERVER['REMOTE_ADDR'];
            }

            return parent::setObjectName(get_class($this) . ':' . $this->getUserName() . '@' . $identity);
        } else {
            return parent::setObjectName($objectName);
        }
    }

}

/**
 * Objekt zákazníka umí navíce od běžného uživatele, počítaní cen, 
 * nákupní košík
 * a obchodní skupiny 
 *
 * @todo přesunout do EaseShop
 *  
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseCustomer extends EaseUser {

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
     * Měna uživatele
     *
     * @var string
     */
    public $Currency = 'Kč';

    /**
     * Vrací (základní) cenu anonymního zákazníka s měnou
     *
     * @param float $productPriceAnon anonymní cena
     * @param int   $productsID       unsigned id produktu v Shopu
     * @param int   $productsPohodaID id produktu z PohodaSQL
     *
     * @return string
     */
    public function showUserPrice($productPriceAnon, $productsID = null, $productsPohodaID = null) {
        return $this->formatPrice($productPriceAnon);
    }

    /**
     * Vrací level uživatele
     *
     * @return int
     */
    public function getUserLevel() {
        return intval($this->userLevel);
    }

}
