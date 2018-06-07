<?php
/**
 * Objekty uživatelů.
 *
 * PHP Version 5
 *
 * @author    Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright 2009-2011 Vitex@hippy.cz (G)
 */

namespace Ease;

/**
 * Třída uživatele.
 *
 * @author  Vítězslav Dvořák <vitex@hippy.cz>
 */
class User extends Anonym
{
    /**
     * Pracujem s tabulkou user.
     *
     * @var string
     */
    public $myTable = 'user';

    /**
     * Klíčový sloupeček tabulky.
     *
     * @var string
     */
    public $keyColumn = 'id';

    /**
     * Sloupecek obsahujici datum vložení záznamu uživatele do shopu.
     *
     * @var string
     */
    public $myCreateColumn = null;

    /**
     * Sloupecek obsahujici datum poslení modifikace záznamu uživatele do shopu.
     *
     * @var string
     */
    public $myLastModifiedColumn = null;

    /**
     * Pole práv uživatele.
     *
     * @var array
     */
    public $permissions = null;

    /**
     * Nactena prava uzivatele.
     *
     * @var array
     */
    public $permissionsInactive = null;  //Prava na ktera jiz uzivatel z duvodu nizkeho levelu nedosahne

    /**
     * Objekt nadřazeného uživatele.
     *
     * @var int unsigned
     */
    public $parent = null;

    /**
     * ID prave nacteneho uzivatele.
     *
     * @var int unsigned
     */
    public $userID = null;

    /**
     * Přihlašovací jméno uživatele.
     *
     * @var string
     */
    public $userLogin = null;

    /**
     * Seznam ID podrizenych uzivatelu.
     *
     * @var array
     */
    public $slaveUsers = null;

    /**
     * Pole uživatelských nastavení.
     *
     * @var array
     */
    public $settings = [];

    /**
     * Sloupeček s loginem.
     *
     * @var string
     */
    public $loginColumn = 'login';

    /**
     * Sloupeček s heslem.
     *
     * @var string
     */
    public $passwordColumn = 'password';

    /**
     * Sloupecek pro docasne zablokovani uctu.
     *
     * @var type
     */
    public $disableColumn = null;

    /**
     * Column for user mail.
     *
     * @var string
     */
    public $mailColumn = 'email';

    /**
     * Sloupeček obsahující serializované rozšířené informace.
     *
     * @var string
     */
    public $settingsColumn = null;

    /**
     * Objekt uživatele aplikace.
     *
     * @param int|string $userID ID nebo Login uživatele jenž se má načíst při
     *                           inicializaci třídy
     */
    public function __construct($userID = null)
    {
        parent::__construct();
        if (!is_null($userID)) {
            if (is_int($userID)) {
                $this->loadFromSQL($userID);
            } else {
                if (isset($this->loginColumn)) {
                    $this->setkeyColumn($this->loginColumn);
                    $this->loadFromSQL($userID);
                    $this->resetObjectIdentity();
                }
            }
        }
        $this->setObjectName();
    }

    /**
     * Give you user name.
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->getDataValue($this->loginColumn);
    }

    /**
     * Retrun user's mail address.
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->getDataValue($this->mailColumn);
    }

    /**
     * Vykreslí GrAvatara uživatele.
     */
    public function draw()
    {
        echo '<img class="avatar" src="'.$this->getIcon().'">';
    }

    /**
     * Vrací odkaz na url ikony.
     *
     * @return string url ikony
     */
    public function getIcon()
    {
        $email = $this->getUserEmail();
        if ($email) {
            return self::getGravatar($email, 800, 'mm', 'g', true,
                    ['title' => $this->getUserName(), 'class' => 'gravatar_icon']);
        } else {
            return;
        }
    }

    /**
     * Pokusí se o přihlášení.
     * Try to Sign in.
     *
     * @param array $formData pole dat z přihlaš. formuláře např. $_REQUEST
     *
     * @return null|boolean
     */
    public function tryToLogin($formData)
    {
        if (!count($formData)) {
            return;
        }
        $login    = $this->dblink->addSlashes($formData[$this->loginColumn]);
        $password = $this->dblink->AddSlashes($formData[$this->passwordColumn]);
        if (!$login) {
            $this->addStatusMessage(_('missing login'), 'error');

            return;
        }
        if (!$password) {
            $this->addStatusMessage(_('missing password'), 'error');

            return;
        }
        $this->setObjectIdentity(['keyColumn' => $this->loginColumn]);
        if ($this->loadFromSQL($login)) {
            $this->setObjectName();
            $this->resetObjectIdentity(['ObjectName']);
            if ($this->passwordValidation($password,
                    $this->getDataValue($this->passwordColumn))) {
                if ($this->isAccountEnabled()) {
                    return $this->loginSuccess();
                } else {
                    $this->userID = null;

                    return false;
                }
            } else {
                $this->userID = null;
                if (count($this->getData())) {
                    $this->addStatusMessage(_('invalid password'), 'error');
                }
                $this->dataReset();

                return false;
            }
        } else {
            $this->addStatusMessage(sprintf(_('user %s does not exist'), $login,
                    'error'));

            return false;
        }
    }

    /**
     * Je učet povolen ?
     *
     * @return bool
     */
    public function isAccountEnabled()
    {
        if (is_null($this->disableColumn)) {
            return true;
        }
        if ($this->getDataValue($this->disableColumn)) {
            $this->addStatusMessage(_('Sign in denied by administrator'),
                'warning');

            return false;
        }

        return true;
    }

    /**
     * Akce provedené po úspěšném přihlášení
     * pokud tam jeste neexistuje zaznam, vytvori se novy.
     */
    public function loginSuccess()
    {
        $this->userID = (int) $this->getMyKey();
        $this->setUserLogin($this->getDataValue($this->loginColumn));
        $this->logged = true;
        $this->addStatusMessage(sprintf(_('Sign in %s all ok'), $this->userLogin),
            'success');
        $this->setObjectName();
        return true;
    }

    /**
     * Načte nastavení uživatele.
     *
     * @param array $settings Serializované pole nastavení
     *
     * @return bool uspěch
     */
    public function loadSettings($settings = null)
    {
        if (is_null($settings)) {
            $settings = $this->getDataValue($this->settingsColumn);
        }
        if ($this->isSerialized($settings)) {
            $this->settings = unserialize($settings);

            return true;
        }

        return false;
    }

    /**
     * Uloží nastavení uživatele.
     *
     * @return int
     */
    public function saveSettings()
    {
        $this->setDataValue($this->settingsColumn,
            $this->dbLink->addSlashes(serialize($this->getSettings())));

        return $this->saveToSQL();
    }

    /**
     * Vrací všechna nastavení uživatele.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Ověření hesla.
     *
     * @param string $plainPassword     heslo v nešifrované podobě
     * @param string $encryptedPassword šifrovné heslo
     *
     * @return bool
     */
    public function passwordValidation($plainPassword, $encryptedPassword)
    {
        if ($plainPassword && $encryptedPassword) {
            $passwordStack = explode(':', $encryptedPassword);
            if (sizeof($passwordStack) != 2) {
                return false;
            }
            if (md5($passwordStack[1].$plainPassword) == $passwordStack[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Zašifruje heslo.
     *
     * @param string $plainTextPassword nešifrované heslo (plaintext)
     *
     * @return string Encrypted password
     */
    public function encryptPassword($plainTextPassword)
    {
        $encryptedPassword = '';
        for ($i = 0; $i < 10; ++$i) {
            $encryptedPassword .= $this->randomNumber();
        }
        $passwordSalt      = substr(md5($encryptedPassword), 0, 2);
        $encryptedPassword = md5($passwordSalt.$plainTextPassword).':'.$passwordSalt;

        return $encryptedPassword;
    }

    /**
     * Změní uživateli uložené heslo.
     *
     * @param string $newPassword nové heslo
     * @param int    $userID      id uživatele
     *
     * @return string password hash
     */
    public function passwordChange($newPassword, $userID = null)
    {
        $hash = null;
        if (empty($userID)) {
            $userID = $this->getUserID();
        }
        if (!empty($userID)) {
            $hash = $this->encryptPassword($newPassword);
            $this->dblink->exeQuery(SQL\SQL::$upd.$this->myTable.' SET '.$this->passwordColumn.'=\''.$hash.'\''.SQL\SQL::$whr.$this->keyColumn.'='.$userID);
            $this->addToLog('PasswordChange: '.$this->getDataValue($this->loginColumn).'@'.$userID.'#'.$this->getDataValue($this->myIDSColumn).' '.$hash);
            if ($userID == $this->getUserID()) {
                $this->setDataValue($this->passwordColumn, $hash);
            }
        }

        return $hash;
    }

    /**
     * Vraci ID přihlášeného uživatele.
     *
     * @return int ID uživatele
     */
    public function getUserID()
    {
        if (isset($this->userID)) {
            return (int) $this->userID;
        }

        return (int) $this->getMyKey();
    }

    /**
     * Vrací login uživatele.
     *
     * @return string
     */
    public function getUserLogin()
    {
        if (!isset($this->userLogin)) {
            return $this->getDataValue($this->loginColumn);
        }

        return $this->userLogin;
    }

    /**
     * Nastavuje login uživatele.
     *
     * @return string
     */
    public function setUserLogin($login)
    {
        $this->userLogin = $login;
        if (isset($this->loginColumn)) {
            return $this->setDataValue($this->loginColumn, $login);
        }

        return $this->userLogin;
    }

    /**
     * Vrací hodnotu uživatelského oprávnění.
     *
     * @param string $permKeyword klíčové slovo oprávnění
     *
     * @return mixed
     */
    public function getPermission($permKeyword = null)
    {
        if (isset($this->permissions[$permKeyword])) {
            return $this->permissions[$permKeyword];
        } else {
            return;
        }
    }

    /**
     * Provede odhlášení uživatele.
     */
    public function logout()
    {
        $this->logged = false;
        $this->addStatusMessage(_('Odhlášení proběhlo uspěšně'), 'success');

        return true;
    }

    /**
     * Vrací hodnotu nastavení.
     *
     * @param string $settingName jméno nastavení
     *
     * @return mixed
     */
    public function getSettingValue($settingName = null)
    {
        if (isset($this->settings[$settingName])) {
            return $this->settings[$settingName];
        } else {
            return;
        }
    }

    /**
     * Nastavuje nastavení.
     *
     * @param array $settings asociativní pole nastavení
     */
    public function setSettings($settings)
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * Nastaví položku nastavení.
     *
     * @param string $settingName  klíčové slovo pro nastavení
     * @param mixed  $settingValue hodnota nastavení
     */
    public function setSettingValue($settingName, $settingValue)
    {
        $this->settings[$settingName] = $settingValue;
    }

    /**
     * Načte oprávnění.
     *
     * @return mixed
     */
    public function loadPermissions()
    {
        return;
    }

    /**
     * Vrací jméno objektu uživatele.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getObjectName();
    }

    /**
     * Uloží pole dat a serializovaná nastavení do SQL.
     * Pokud je $SearchForID 0 updatuje pokud ze nastaven  keyColumn.
     *
     * @param array $data        asociativní pole dat
     * @param bool  $searchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToSQL($data = null, $searchForID = false)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        if (!is_null($this->settingsColumn)) {
            $data[$this->settingsColumn] = serialize($this->settings);
        }

        return parent::saveToSQL($data, $searchForID);
    }

    /**
     * Načte z SQL data k aktuálnímu $ItemID a případně aplikuje
     * nastavení.
     *
     * @param int  $itemID     id záznamu k načtení
     * @param bool $multiplete nevarovat v případě vícenásobného
     *                         výsledku
     *
     * @return null|integer Results
     */
    public function loadFromSQL($itemID = null, $multiplete = false)
    {
        $result = parent::loadFromSQL($itemID, $multiplete);
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
     * @param integer $size      Size in pixels, defaults to 80px [ 1 - 512 ]
     * @param string $default   [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $maxRating Maximum rating (inclusive) [ g | pg | r | x ]
     *
     * @return string containing either just a URL or a complete image tag
     *
     * @source http://gravatar.com/site/implement/images/php/
     */
    public static function getGravatar(
        $email, $size = 80, $default = 'mm', $maxRating = 'g'
    )
    {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$size&d=$default&r=$maxRating";

        return $url;
    }

    /**
     * Nastavení jména objektu uživatele.
     *
     * @param string $objectName vynucené jméno objektu
     *
     * @return string
     */
    public function setObjectName($objectName = null)
    {

        if (empty($objectName) && isset($_SERVER['REMOTE_ADDR'])) {
            $name = parent::setObjectName(get_class($this).':'.$this->getUserName().'@'.self::remoteToIdentity());
        } else {
            $name = parent::setObjectName($objectName);
        }
        return $name;
    }
}
