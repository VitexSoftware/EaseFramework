<?php
/**
 * Zakladni objekt urceny k rodicovstvi vsem pouzivanym objektum.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2017 Vitex@hippy.cz (G)
 */

namespace Ease;

/**
 * Zakladni objekt urceny k rodicovstvi vsem pouzivanym objektum.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
class Sand extends Atom
{
    /**
     * Default Language Code.
     *
     * @var string
     */
    public $langCode = null;

    /**
     * Common object data holder.
     *
     * @var array
     */
    public $data = null;

    /**
     * Obsahuje všechna pole souhrně považovaná za identitu. Toto pole je plněno
     * v metodě SaveObjectIdentity {volá se automaticky v EaseSand::__construct()}.
     *
     * @var array
     */
    public $identity = [];

    /**
     * Původní identita sloužící jako záloha k zrekonstruování počátečního stavu objektu.
     *
     * @var array
     */
    public $initialIdenty = [];

    /**
     * Tyto sloupecky jsou uchovavany pri operacich s identitou objektu.
     *
     * @var array
     */
    public $identityColumns = ['ObjectName',
        'myKeyColumn',
        'myTable',
        'MyIDSColumn',
        'MyRefIDColumn',
        'myCreateColumn',
        'myLastModifiedColumn',];

    /**
     * Klíčový sloupeček v používané MySQL tabulce.
     *
     * @var string
     */
    public $myKeyColumn = 'id';

    /**
     * Synchronizační sloupeček. napr products_ids.
     *
     * @var string
     */
    public $myIDSColumn = null;

    /**
     * Sloupeček obsahující datum vložení záznamu do shopu.
     *
     * @var string
     */
    public $myCreateColumn = null;

    /**
     * Slopecek obsahujici datum poslení modifikace záznamu do shopu.
     *
     * @var string
     */
    public $myLastModifiedColumn = null;

    /**
     * Objekt pro logování.
     *
     * @var Logger\Regent
     */
    public $logger = null;

    /**
     * Odkaz na vlastnící objekt.
     *
     * @var Sand|mixed object
     */
    public $parentObject = null;

    /**
     * Sdílený objekt frameworku.
     *
     * @var Shared
     */
    public $easeShared = null;

    /**
     * Prapředek všech objektů.
     */
    public function __construct()
    {
        $this->easeShared = Shared::singleton();
        $this->logger     = $this->easeShared->logger();

        $this->setObjectName();
        $this->initialIdenty = $this->saveObjectIdentity();
    }

    /**
     * Přidá zprávu do sdíleného zásobníku pro zobrazení uživateli.
     *
     * @param string $message  Text zprávy
     * @param string $type     Fronta zpráv (warning|info|error|success)
     *
     * @return
     */
    public function addStatusMessage($message, $type = 'info')
    {
        return $this->easeShared->takeMessage(new Logger\Message($message,
                $type, get_class($this)));
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
        $messages = array_merge($this->statusMessages,
            $this->logger->statusMessages,
            Shared::logger()->getStatusMessages(),
            Shared::instanced()->getStatusMessages());
        if ($clean) {
            $this->cleanMessages();
        }

        return $messages;
    }

    /**
     * Vymaže zprávy.
     */
    public function cleanMessages()
    {
        parent::cleanMessages();
        $this->logger->cleanMessages();

        return Shared::instanced()->cleanMessages();
    }

    /**
     * Připojí ke stávajícímu objektu přiřazený objekt.
     *
     * @param string $propertyName název proměnné
     * @param object $object       přiřazovaný objekt
     */
    public function attachObject($propertyName, $object)
    {
        if (is_object($object)) {
            $this->$propertyName = &$object;
        }
    }

    /**
     * Nastaví jméno objektu.
     *
     * @param string $objectName
     *
     * @return string Jméno objektu
     */
    public function setObjectName($objectName = null)
    {
        if (empty($objectName)) {
            $this->objectName = get_class($this);
        } else {
            $this->objectName = $objectName;
        }

        return $this->objectName;
    }

    /**
     * Vrací jméno objektu.
     *
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Nastaví novou identitu objektu.
     *
     * @param array $newIdentity
     */
    public function setObjectIdentity($newIdentity)
    {
        $changes = 0;
        $this->saveObjectIdentity();
        foreach ($this->identityColumns as $column) {
            if (isset($newIdentity[$column])) {
                $this->$column = $newIdentity[$column];
                ++$changes;
            }
        }

        return $changes;
    }

    /**
     * Uloží identitu objektu do pole $this->identity.
     *
     * @return array pole s identitou
     */
    public function saveObjectIdentity()
    {
        foreach ($this->identityColumns as $column) {
            if (isset($this->$column)) {
                $this->identity[$column] = $this->$column;
            }
        }

        return $this->identity;
    }

    /**
     * Obnoví uloženou identitu objektu.
     *
     * @param array $identity pole s identitou např. array('myTable'=>'user');
     */
    public function restoreObjectIdentity($identity = null)
    {
        if (is_null($identity)) {
            foreach ($this->identityColumns as $column) {
                if (isset($this->identity[$column])) {
                    $this->$column = $this->identity[$column];
                }
            }
        } else {
            foreach ($identity as $column) {
                if (isset($this->identity[$column])) {
                    $this->$column = $this->identity[$column];
                }
            }
        }
    }

    /**
     * Obnoví poslední použitou identitu.
     */
    public function resetObjectIdentity()
    {
        $this->identity = $this->initialIdenty;
        $this->restoreObjectIdentity();
    }

    /**
     * Z datového pole $SourceArray přemístí políčko $ColumName do pole
     * $destinationArray.
     *
     * @param array  $sourceArray      zdrojové pole dat
     * @param array  $destinationArray cílové pole dat
     * @param string $columName        název položky k převzetí
     */
    public static function divDataArray(&$sourceArray, &$destinationArray,
                                        $columName)
    {
        $result = false;
        if (array_key_exists($columName, $sourceArray)) {
            $destinationArray[$columName] = $sourceArray[$columName];
            unset($sourceArray[$columName]);

            $result = true;
        }

        return $result;
    }

    /**
     * Vynuluje všechny pole vlastností objektu.
     */
    public function dataReset()
    {
        $this->data = [];
    }

    /**
     * Načte $data do polí objektu.
     *
     * @param array $data  asociativní pole dat
     * @param bool  $reset vyprazdnit pole před naplněním ?
     *
     * @return int počet načtených položek
     */
    public function setData($data, $reset = false)
    {
        $ret = null;
        if (!is_null($data) && count($data)) {
            if ($reset) {
                $this->dataReset();
            }
            if (is_array($this->data)) {
                $this->data = array_merge($this->data, $data);
            } else {
                $this->data = $data;
            }
            $ret = count($data);
        }

        return $ret;
    }

    /**
     * Vrací celé pole dat objektu.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Vrací počet položek dat objektu.
     *
     * @return int
     */
    public function getDataCount()
    {
        return count($this->data);
    }

    /**
     * Vrací hodnotu z pole dat pro MySQL.
     *
     * @param string $columnName název hodnoty/sloupečku
     *
     * @return mixed
     */
    public function getDataValue($columnName)
    {
        if (isset($this->data[$columnName])) {
            return $this->data[$columnName];
        }

        return;
    }

    /**
     * Nastaví hodnotu poli objektu.
     *
     * @param string $columnName název datové kolonky
     * @param mixed  $value      hodnota dat
     *
     * @return bool Success
     */
    public function setDataValue($columnName, $value)
    {
        $this->data[$columnName] = $value;

        return true;
    }

    /**
     * Odstrani polozku z pole dat pro MySQL.
     *
     * @param string $columnName název klíče k vymazání
     *
     * @return bool success
     */
    public function unsetDataValue($columnName)
    {
        if (array_key_exists($columnName, $this->data)) {
            unset($this->data[$columnName]);

            return true;
        }

        return false;
    }

    /**
     * Převezme data do aktuálního pole dat.
     *
     * @param array $data asociativní pole dat
     *
     * @return int
     */
    public function takeData($data)
    {
        if (is_array($this->data)) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data = $data;
        }

        return count($data);
    }

    /**
     * Odstraní z textu diakritiku.
     *
     * @param string $text
     */
    public static function rip($text)
    {
        $convertTable = [
            'ä' => 'a',
            'Ä' => 'A',
            'á' => 'a',
            'Á' => 'A',
            'à' => 'a',
            'À' => 'A',
            'ã' => 'a',
            'Ã' => 'A',
            'â' => 'a',
            'Â' => 'A',
            'č' => 'c',
            'Č' => 'C',
            'ć' => 'c',
            'Ć' => 'C',
            'ď' => 'd',
            'Ď' => 'D',
            'ě' => 'e',
            'Ě' => 'E',
            'é' => 'e',
            'É' => 'E',
            'ë' => 'e',
            'Ë' => 'E',
            'è' => 'e',
            'È' => 'E',
            'ê' => 'e',
            'Ê' => 'E',
            'í' => 'i',
            'Í' => 'I',
            'ï' => 'i',
            'Ï' => 'I',
            'ì' => 'i',
            'Ì' => 'I',
            'î' => 'i',
            'Î' => 'I',
            'ľ' => 'l',
            'Ľ' => 'L',
            'ĺ' => 'l',
            'Ĺ' => 'L',
            'ń' => 'n',
            'Ń' => 'N',
            'ň' => 'n',
            'Ň' => 'N',
            'ñ' => 'n',
            'Ñ' => 'N',
            'ó' => 'o',
            'Ó' => 'O',
            'ö' => 'o',
            'Ö' => 'O',
            'ô' => 'o',
            'Ô' => 'O',
            'ò' => 'o',
            'Ò' => 'O',
            'õ' => 'o',
            'Õ' => 'O',
            'ő' => 'o',
            'Ő' => 'O',
            'ř' => 'r',
            'Ř' => 'R',
            'ŕ' => 'r',
            'Ŕ' => 'R',
            'š' => 's',
            'Š' => 'S',
            'ś' => 's',
            'Ś' => 'S',
            'ť' => 't',
            'Ť' => 'T',
            'ú' => 'u',
            'Ú' => 'U',
            'ů' => 'u',
            'Ů' => 'U',
            'ü' => 'u',
            'Ü' => 'U',
            'ù' => 'u',
            'Ù' => 'U',
            'ũ' => 'u',
            'Ũ' => 'U',
            'û' => 'u',
            'Û' => 'U',
            'ý' => 'y',
            'Ý' => 'Y',
            'ž' => 'z',
            'Ž' => 'Z',
            'ź' => 'z',
            'Ź' => 'Z',
        ];

        return @iconv('UTF-8', 'ASCII//TRANSLIT', strtr($text, $convertTable));
    }

    /**
     * Encrypt.
     * Šifrování.
     *
     * @param string $textToEncrypt plaintext
     * @param string $encryptKey    klíč
     *
     * @return string encrypted text
     */
    public static function easeEncrypt($textToEncrypt, $encryptKey)
    {
        $encryption_key = base64_decode($encryptKey);
        $iv             = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted      = openssl_encrypt($textToEncrypt, 'aes-256-cbc',
            $encryption_key, 0, $iv);
        return base64_encode($encrypted.'::'.$iv);
    }

    /**
     * Decrypt
     * Dešifrování.
     *
     * @param string $textToDecrypt šifrovaný text
     * @param string $encryptKey    šifrovací klíč
     *
     * @return string
     */
    public static function easeDecrypt($textToDecrypt, $encryptKey)
    {
        $encryption_key = base64_decode($encryptKey);
        list($encrypted_data, $iv) = explode('::',
            base64_decode($textToDecrypt), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key,
            0, $iv);
    }

    /**
     * Generování náhodného čísla.
     *
     * @param int $minimal
     * @param int $maximal
     *
     * @return float
     */
    public static function randomNumber($minimal = null, $maximal = null)
    {
        mt_srand((float) microtime() * 1000000);
        if (isset($minimal) && isset($maximal)) {
            if ($minimal >= $maximal) {
                $rand = false;
            } else {
                $rand = mt_rand($minimal, $maximal);
            }
        } else {
            $rand = mt_rand();
        }

        return $rand;
    }

    /**
     * Vrací náhodný řetězec dané délky.
     *
     * @param int $length
     *
     * @return string
     */
    public static function randomString($length = 6)
    {
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'),
            0, $length);
    }

    /**
     * Rekurzivně překóduje pole.
     *
     * @param string $in_charset
     * @param string $out_charset
     * @param array  $arr         originální pole
     *
     * @return array překódované pole
     */
    public function recursiveIconv($in_charset, $out_charset, $arr)
    {
        if (!is_array($arr)) {
            return iconv($in_charset, $out_charset, $arr);
        }
        $ret = $arr;
        array_walk_recursive($ret, [$this, 'arrayIconv'],
            [$in_charset, $out_charset]);

        return $ret;
    }

    /**
     * Pomocná funkce pro překódování vícerozměrného pole.
     *
     * @see recursiveIconv
     *
     * @param mixed  $val
     * @param string $key
     * @param mixed  $encodings
     */
    public function arrayIconv(&$val, $key, $encodings)
    {
        $val = iconv($encodings[0], $encodings[1], $val);
    }

    /**
     * Zapíše zprávu do logu.
     *
     * @param string $message zpráva
     * @param string $type    typ zprávy (info|warning|success|error|*)
     *
     * @return bool byl report zapsán ?
     */
    public function addToLog($message, $type = 'message')
    {
        $logged = false;
        if (is_object($this->logger)) {
            $this->logger->addToLog($this->getObjectName(), $message, $type);
        } else {
            $logged = Shared::logger()->addToLog($this->getObjectName(),
                $message, $type);
        }

        return $logged;
    }

    /**
     * Magická funkce pro všechny potomky.
     *
     * @return string
     */
    public function __toString()
    {
        return 'Object: '.$this->getObjectName();
    }

    /**
     * Pro serializaci připraví vše.
     *
     * @return array
     */
    public function __sleep()
    {
        $objectVars = array_keys(get_object_vars($this));
        $parent     = get_parent_class(__CLASS__);
        if (method_exists($parent, '__sleep') && ($parent != 'Ease\Atom')) {
            $parentObjectVars = parent::__sleep();
            array_push($objectVars, $parentObjectVars);
        }
        $this->saveObjectIdentity();

        return $objectVars;
    }

    /**
     * Zobrazí velikost souboru v srozumitelném tvaru.
     *
     * @param int $filesize bytů
     *
     * @return string
     */
    public static function humanFilesize($filesize)
    {
        if (is_numeric($filesize)) {
            $decr   = 1024;
            $step   = 0;
            $prefix = ['Byte', 'KB', 'MB', 'GB', 'TB', 'PB'];

            while (($filesize / $decr) > 0.9) {
                $filesize = $filesize / $decr;
                ++$step;
            }

            return round($filesize, 2).' '.$prefix[$step];
        } else {
            return 'NaN';
        }
    }

    /**
     * Reindex Array by given key.
     *
     * @param array  $data    array to reindex
     * @param string $indexBy one of columns in array
     *
     * @return array
     */
    public static function reindexArrayBy($data, $indexBy = null)
    {
        $reindexedData = [];

        foreach ($data as $dataID => $data) {
            if (array_key_exists($indexBy, $data)) {
                $reindexedData[$data[$indexBy]] = $data;
            } else {
                throw new \Exception(sprintf('Data row does not contain column %s for reindexing',
                    $indexBy));
            }
        }

        return $reindexedData;
    }

    /**
     * Filter Only letters from string.
     * Pouze malé a velké písmena.
     *
     * @return string text bez zvláštních znaků
     */
    public static function lettersOnly($text)
    {
        return preg_replace('/[^(a-zA-Z0-9)]*/', '', $text);
    }

    /**
     * Confirm that string is serialized
     * 
     * @param string $data
     *
     * @return boolean
     */
    public static function is_serialized($data)
    {
        // if it isn't a string, it isn't serialized
        if (!is_string($data)) return false;
        $data = trim($data);
        if ('N;' == $data) return true;
        if (!preg_match('/^([adObis]):/', $data, $badions)) return false;
        switch ($badions[1]) {
            case 'a' :
            case 'O' :
            case 's' :
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                        return true;
                break;
            case 'b' :
            case 'i' :
            case 'd' :
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                        return true;
                break;
        }
        return false;
    }

    /**
     * Akce po probuzení ze serializace.
     */
    public function __wakeup()
    {
        $this->setObjectName();
        $this->restoreObjectIdentity();
    }

}
