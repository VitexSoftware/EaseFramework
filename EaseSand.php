<?php

/**
 * Zakladni objekt urceny k rodicovstvi vsem pouzivanym objektum
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2014 Vitex@hippy.cz (G)
 */
require_once 'EaseAtom.php';
require_once 'EaseLogger.php';
require_once 'EaseShared.php';

/**
 * Zakladni objekt urceny k rodicovstvi vsem pouzivanym objektum
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
class EaseSand extends EaseAtom
{

    /**
     * Nazev jazyka
     * @var string
     */
    public $langCode = null;

    /**
     * data držená objektem
     * @var array
     */
    public $data = null;

    /**
     * Výchozí index pole pro držení dat
     * @var type
     */
    public $defaultDataPrefix = null;

    /**
     * Obsahuje všechna pole souhrně považovaná za identitu. Toto pole je plněno
     * v metodě SaveObjectIdentity {volá se automaticky v EaseSand::__construct()}
     * @var array
     */
    public $identity = array();

    /**
     * Původní identita sloužící jako záloha k zrekonstruování počátečního stavu objektu.
     * @var array
     */
    public $initialIdenty = array();

    /**
     * Tyto sloupecky jsou uchovavany pri operacich s identitou objektu
     * @var array
     */
    public $identityColumns = array('ObjectName',
        'myKeyColumn', 'MSKeyColumn',
        'myTable', 'MSTable',
        'MyIDSColumn', 'MSIDSColumn',
        'MyRefIDColumn', 'MSRefIDColumn',
        'myCreateColumn', 'MSCreateColumn',
        'myLastModifiedColumn', 'MSLastModifiedColumn');

    /**
     * Klíčový sloupeček v používané MSSQL tabulce
     * @var string
     */
    public $MSKeyColumn = 'ID';

    /**
     * Klíčový sloupeček v používané MySQL tabulce
     * @var string
     */
    public $myKeyColumn = 'id';

    /**
     * Synchronizační sloupeček. napr products_ids
     * @var string
     */
    public $myIDSColumn = null;

    /**
     * Synchronizační sloupeček. napr IDS
     * @var string
     */
    public $msIDSColumn = null;

    /**
     * Synchronizační sloupeček. napr products_MSSQL_id
     * @var string
     */
    public $myRefIDColumn = null;

    /**
     * Synchronizační sloupeček. napr
     * @var string
     */
    public $msRefIDColumn = null;

    /**
     * Sloupeček obsahující datum vložení záznamu do shopu
     * @var string
     */
    public $myCreateColumn = null;

    /**
     *  Sloupeček obsahujíci datum vložení záznamu do Pohody
     * @var string
     */
    public $msCreateColumn = null;

    /**
     * Slopecek obsahujici datum poslení modifikace záznamu do shopu
     * @var string
     */
    public $myLastModifiedColumn = null;

    /**
     * Slopecek obsahujici datum poslení modifikace záznamu do Pohody
     * @var string
     */
    public $msLastModifiedColumn = null;

    /**
     * Objekt pro logování
     * @var EaseLogger
     */
    public $logger = null;

    /**
     * Jakým objektem řešit logování ?
     * @var EaseLogger
     */
    public $logType = 'EaseLogger';

    /**
     * Odkaz na vlastnící objekt
     * @var EaseSand|mixed object
     */
    public $parentObject = null;

    /**
     * Sdílený objekt frameworku
     * @var EaseShared
     */
    public $easeShared = null;

    /**
     * Prapředek všech objektů
     */
    public function __construct()
    {
        $this->easeShared = EaseShared::singleton();
        if ($this->logType != 'none') {
            $this->logger = EaseLogger::singleton();
        }
        $this->setObjectName();
        $this->initialIdenty = $this->saveObjectIdentity();
    }

    /**
     * Přidá zprávu do sdíleného zásobníku pro zobrazení uživateli
     *
     * @param string  $message  Text zprávy
     * @param string  $type     Fronta zpráv (warning|info|error|success)
     * @param boolean $addIcons přidá UTF8 ikonky na začátek zpráv
     * @param boolean $addToLog zapisovat zpravu do logu ?
     *
     * @return
     */
    public function addStatusMessage($message, $type = 'info', $addIcons = true, $addToLog = true)
    {
        return EaseShared::instanced()->addStatusMessage($message, $type, $addIcons, $addToLog);
    }

    /**
     * Připojí ke stávajícímu objektu přiřazený objekt
     *
     * @param string $propertyName název proměnné
     * @param object $object       přiřazovaný objekt
     */
    public function attachObject($propertyName, $object)
    {
        if (is_object($object)) {
            $this->$propertyName = & $object;
        }
    }

    public function xAttachObject($variableName, $ObjectName, $ObjectParams = null)
    {

        if (!is_null($this->$variableName)) {
            if (!is_object($this->$variableName)) {
                die('AttachObject: UndefinedProperty $this->' . $variableName . ' of ' . $this->getObjectName());
            }
        } else {

            if (class_exists($ObjectName)) {

                if (property_exists($this, 'parentObject') && is_object($this->parentObject)) {
                    //Pokud již rodičovský objekt obsahuje požadovanou vlastnost, pouze na ní odkážeme
                    if (property_exists($this->parentObject, $variableName)) {
                        $this->$variableName = & $this->parentObject->$variableName;
                    }
                } else {
                    //jinak se vytvoří nová instance objektu
                    $NumberOfArgs = func_num_args();
                    if ($NumberOfArgs == 2) {
                        $this->$variableName = new $ObjectName();
                    } else {
                        $Arguments = func_get_args();
                        array_shift($Arguments);
                        array_shift($Arguments);
                        eval('$this->' . $variableName . ' = new ' . $ObjectName . '(' . implode(',', $Arguments) . ');');
                    }
                }
            }

            return true;
        }

        if (property_exists($this->$variableName, 'parentObject')) {
            $this->$variableName->parentObject = & $this;
        }
    }

    /**
     * Nastaví jméno objektu
     *
     * @param string $objectName
     *
     * @return string Jméno objektu
     */
    public function setObjectName($objectName = null)
    {
        if ($objectName) {
            $this->objectName = $objectName;
        } else {
            $this->objectName = get_class($this);
        }

        return $this->objectName;
    }

    /**
     * Vrací jméno objektu
     *
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * Nastaví novou identitu objektu
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
                $changes++;
            }
        }

        return $changes;
    }

    /**
     * Uloží identitu objektu do pole $this->Identity
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
     * Obnoví uloženou identitu objektu
     *
     * @param array $identity pole s identitou např. array('myTable'=>'user');
     */
    public function restoreObjectIdentity($identity = null)
    {
        foreach ($this->identityColumns as $column) {
            if (isset($this->identity[$column])) {
                $this->$column = $this->identity[$column];
            }
        }
    }

    /**
     * Obnoví poslední použitou identitu
     */
    public function resetObjectIdentity()
    {
        $this->identity = $this->initialIdenty;
        $this->restoreObjectIdentity();
    }

    /**
     * Z datového pole $SourceArray přemístí políčko $ColumName do pole
     * $DestinationArray
     *
     * @param array  $sourceArray      zdrojové pole dat
     * @param array  $destinationArray cílové pole dat
     * @param string $columName        název položky k převzetí
     */
    public static function divDataArray(& $sourceArray, & $destinationArray, $columName)
    {
        if (array_key_exists($columName, $sourceArray)) {
            $destinationArray[$columName] = $sourceArray[$columName];
            unset($sourceArray[$columName]);

            return true;
        }

        return false;
    }

    /**
     * Vynuluje všechny pole vlastností objektu
     *
     * @param array $dataPrefix název datové skupiny
     */
    public function dataReset($dataPrefix = null)
    {
        if (!$dataPrefix) {
            $dataPrefix = $this->defaultDataPrefix;
        }
        if ($dataPrefix) {
            $this->data[$dataPrefix] = array();
        } else {
            $this->data = array();
        }
    }

    /**
     * Načte $data do polí objektu
     *
     * @param array  $data       asociativní pole dat
     * @param string $dataPrefix prefix skupiny dat (např. "MSSQL")
     * @param bool   $reset      vyprazdnit pole před naplněním ?
     *
     * @return int počet načtených položek
     */
    public function setData($data, $dataPrefix = null, $reset = false)
    {
        if (is_null($data) || !count($data)) {
            return null;
        }
        if (!$dataPrefix) {
            $dataPrefix = $this->defaultDataPrefix;
        }
        if ($reset) {
            $this->dataReset($dataPrefix);
        }
        if ($dataPrefix) {
            if (isset($this->data[$dataPrefix]) && is_array($this->data[$dataPrefix])) {
                $this->data[$dataPrefix] = array_merge($this->data[$dataPrefix], $data);
            } else {
                $this->data[$dataPrefix] = $data;
            }
        } else {
            if (is_array($this->data)) {
                $this->data = array_merge($this->data, $data);
            } else {
                $this->data = $data;
            }
        }

        return count($data);
    }

    /**
     * Vrací celé pole dat objektu
     *
     * @param string $dataPrefix
     *
     * @return array
     */
    public function getData($dataPrefix = null)
    {
        if (is_null($dataPrefix)) {
            $dataPrefix = $this->defaultDataPrefix;
        }
        if ($dataPrefix) {
            if (isset($this->data[$dataPrefix])) {
                return $this->data[$dataPrefix];
            }

            return null;
        } else {
            return $this->data;
        }
    }

    /**
     * Vrací počet položek dat objektu
     *
     * @param string $dataPrefix
     *
     * @return int
     */
    public function getDataCount($dataPrefix = null)
    {
        $counter = 0;
        if (!$dataPrefix) {
            $dataPrefix = $this->defaultDataPrefix;
        }
        if ($dataPrefix) {
            return count($this->data[$dataPrefix]);
        } else {
            return count($this->data);
        }
    }

    /**
     * Vrací hodnotu z pole dat pro MySQL
     *
     * @param string $columnName název hodnoty/sloupečku
     * @param string $dataPrefix
     *
     * @return mixed
     */
    public function getDataValue($columnName, $dataPrefix = null)
    {
        if (is_null($dataPrefix)) {
            $dataPrefix = $this->defaultDataPrefix;
        }
        if ($dataPrefix) {
            if (isset($this->data[$dataPrefix]) && isset($this->data[$dataPrefix][$columnName])) {
                return $this->data[$dataPrefix][$columnName];
            }
        } else {
            if (isset($this->data[$columnName])) {
                return $this->data[$columnName];
            }
        }

        return null;
    }

    /**
     * Nastaví hodnotu poli objektu
     *
     * @param string $columnName název datové kolonky
     * @param mixed  $value      hodnota dat
     * @param string $dataPrefix prefix skupiny dat
     *
     * @return boolean Success
     */
    public function setDataValue($columnName, $value, $dataPrefix = null)
    {
        if (!$dataPrefix) {
            $dataPrefix = $this->defaultDataPrefix;
        }
        if ($dataPrefix) {
            $this->data[$dataPrefix][$columnName] = $value;
        } else {
            $this->data[$columnName] = $value;
        }

        return true;
    }

    /**
     * Odstrani polozku z pole dat pro MySQL
     *
     * @param string $columnName název klíče k vymazání
     * @param string $dataPrefix prefix skupiny dat
     *
     * @return boolean success
     */
    public function unsetDataValue($columnName, $dataPrefix = null)
    {
        if (!$dataPrefix) {
            $dataPrefix = $this->defaultDataPrefix;
        }
        if ($dataPrefix) {
            if (isset($this->data[$dataPrefix][$columnName])) {
                unset($this->data[$dataPrefix][$columnName]);

                return true;
            }
        }
        if (isset($this->data[$columnName])) {
            unset($this->data[$columnName]);

            return true;
        }

        return false;
    }

    /**
     * Převezme data do aktuálního pole dat
     *
     * @param array  $data       asociativní pole dat
     * @param string $dataPrefix prefix datové skupiny
     *
     * @return int
     */
    public function takeData($data, $dataPrefix = null)
    {
        if (!$dataPrefix) {
            $dataPrefix = $this->defaultDataPrefix;
        }
        if ($dataPrefix) {
            if (isset($this->data[$dataPrefix]) && is_array($this->data[$dataPrefix])) {
                $this->data[$dataPrefix] = array_merge($this->data[$dataPrefix], $data);
            } else {
                $this->data[$dataPrefix] = $data;
            }
        } else {
            if (is_array($this->data)) {
                $this->data = array_merge($this->data, $data);
            } else {
                $this->data = $data;
            }
        }

        return count($data);
    }

    /**
     * Funkce pro defaultní slashování v celém frameworku
     *
     * @param string $text
     *
     * @return string
     */
    public function easeAddSlashes($text)
    {
        return addSlashes($text);
    }

    /**
     * Zobrazí obsah pole nebo objektu
     *
     * @param mixed  $argument All used by print_r() function
     * @param string $comment  hint při najetí myší
     */
    public function printPre($argument, $comment = '')
    {
        $retVal = '';
        $itemsCount = 0;
        if (is_object($argument)) {
            $itemsCount = count($argument);
            $comment = gettype($argument) . ': ' . get_class($argument) . ': ' . $comment;
        } else {
            $comment = gettype($argument) . ': ' . $comment;

            if (is_array($argument)) {
                $itemsCount = count($argument);
            }
        }

        if ($itemsCount) {
            $comment .= " ($itemsCount)";
        }

        if ($this->easeShared->runType == 'web') {
            $retVal .= '<pre class="debug" style="overflow: scroll; border: solid 1px green;  color: white; background-color: black;" title="' . $comment . '">';
        } else {
            $retVal .= "\n########### $comment ###########\n";
        }

        $retVal .= $this->PrintPreBasic($argument);

        if ($this->easeShared->runType == 'web') {
            $retVal .= '</pre>';
        } else {
            $retVal .= "\n";
        }

        return $retVal;
    }

    /**
     * Vrací print_pre($Argument) jako řetězec
     *
     * @param mixed $argument
     *
     * @return string
     */
    public static function printPreBasic($argument)
    {
        return print_r($argument, true);
    }

    /**
     * Vrací utf8 podřetězec
     *
     * @param string $str    utf8
     * @param int    $string offset
     * @param int    $length length
     *
     * @return string utf8
     */
    public static function substrUnicode($str, $string, $length = null)
    {
        return join("", array_slice(preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $string, $length));
    }

    /**
     * Odstraní z textu diakritiku
     *
     * @param string $text
     */
    public static function rip($text)
    {
        $convertTable = Array(
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
            'Ź' => 'Z'
        );

        return @iconv('UTF-8', 'ASCII//TRANSLIT', strtr($text, $convertTable));
    }

    /**
     * Šifrování
     *
     * @param string $textToEncrypt plaintext
     * @param string $encryptKey    klíč
     *
     * @return string encrypted text
     */
    public static function easeEncrypt($textToEncrypt, $encryptKey)
    {
        srand((double) microtime() * 1000000); //for sake of MCRYPT_RAND
        $encryptKey = md5($encryptKey);
        $encryptHandle = mcrypt_module_open('des', '', 'cfb', '');
        $encryptKey = substr($encryptKey, 0, mcrypt_enc_get_key_size($encryptHandle));
        $initialVectorSize = mcrypt_enc_get_iv_size($encryptHandle);
        $initialVector = mcrypt_create_iv($initialVectorSize, MCRYPT_RAND);
        if (mcrypt_generic_init($encryptHandle, $encryptKey, $initialVector) != - 1) {
            $encryptedText = mcrypt_generic($encryptHandle, $TextToEncrypt);
            mcrypt_generic_deinit($encryptHandle);
            mcrypt_module_close($encryptHandle);
            $encryptedText = $initialVector . $encryptedText;

            return $encryptedText;
        }
    }

    /**
     * Dešifrování
     *
     * @param string $textToDecrypt šifrovaný text
     * @param string $encryptKey    šifrovací klíč
     *
     * @return string
     */
    public static function easeDecrypt(string $textToDercypt, $encryptKey)
    {
        $encryptKey = md5($encryptKey);
        $encryptHandle = mcrypt_module_open('des', '', 'cfb', '');
        $encryptKey = substr($encryptKey, 0, mcrypt_enc_get_key_size($encryptHandle));
        $InitialVectorSize = mcrypt_enc_get_iv_size($encryptHandle);
        $initialVector = substr($textToDecrypt, 0, $InitialVectorSize);
        $textToDecrypt = substr($textToDecrypt, $InitialVectorSize);
        if (mcrypt_generic_init($encryptHandle, $encryptKey, $initialVector) != - 1) {
            $decryptedText = mdecrypt_generic($encryptHandle, $textToDecrypt);
            mcrypt_generic_deinit($encryptHandle);
            mcrypt_module_close($encryptHandle);

            return $decryptedText;
        }
    }

    /**
     * Generování náhodného čísla
     *
     * @param int $minimal
     * @param int $maximal
     *
     * @return float
     */
    public static function randomNumber($minimal = null, $maximal = null)
    {
        mt_srand((double) microtime() * 1000000);
        if (isset($minimal) && isset($maximal)) {
            if ($minimal >= $maximal) {
                return $minimal;
            } else {
                return mt_rand($minimal, $maximal);
            }
        } else {
            return mt_rand();
        }
    }

    /**
     * Vrací náhodný řetězec dané délky
     *
     * @param int $length
     *
     * @return string
     */
    public static function randomString($length = 6)
    {
        return substr(str_replace("/", "A", str_replace(".", "X", crypt(strval(self::randomNumber(1000, 9000)),$length))), 3, $length);
    }

    /**
     * Oveření mailu
     *
     * @param string $email    mailová adresa
     * @param bool   $checkDNS testovat DNS ?
     *
     * @package	  isemail
     * @author	  Dominic Sayers <dominic_sayers@hotmail.com>
     * @copyright 2009 Dominic Sayers
     * @license	  http://www.opensource.org/licenses/cpal_1.0 Common Public Attribution License Version 1.0 (CPAL) license
     * @link	  http://www.dominicsayers.com/isemail
     * @version	  1.9 - Minor modifications to make it compatible with PHPLint
     */
    public function isEmail($email, $checkDNS = false)
    {
        /* Check that $email is a valid address. Read the following RFCs to understand the constraints:
          // 	(http://tools.ietf.org/html/rfc5322)
          // 	(http://tools.ietf.org/html/rfc3696)
          // 	(http://tools.ietf.org/html/rfc5321)
          // 	(http://tools.ietf.org/html/rfc4291#section-2.2)
          // 	(http://tools.ietf.org/html/rfc1123#section-2.1)
          // the upper limit on address lengths should normally be considered to be 256
          // 	(http://www.rfc-editor.org/errata_search.php?rfc=3696)
          // 	NB I think John Klensin is misreading RFC 5321 and the the limit should actually be 254
          // 	However, I will stick to the published number until it is changed.
          //
          // The maximum total length of a reverse-path or forward-path is 256
          // characters (including the punctuation and element separators)
          // 	(http://tools.ietf.org/html/rfc5321#section-4.5.3.1.3)
         * */
        $emailLength = strlen($email);
        if ($emailLength > 256)
            return false; // Too long
            /*
              // Contemporary email addresses consist of a "local part" separated from
              // a "domain part" (a fully-qualified domain name) by an at-sign ("@").
              // 	(http://tools.ietf.org/html/rfc3696#section-3)
             */
        $atIndex = strrpos($email, '@');

        if ($atIndex === false)
            return false; // No at-sign
        if ($atIndex === 0)
            return false; // No local part
        if ($atIndex === $emailLength)
            return false; // No domain part
// Sanitize comments
// - remove nested comments, quotes and dots in comments
// - remove parentheses and dots from quoted strings
        $braceDepth = 0;
        $inQuote = false;
        $escapeThisChar = false;

        for ($i = 0; $i < $emailLength; ++$i) {
            $char = $email[$i];
            $replaceChar = false;

            if ($char === '\\') {
                $escapeThisChar = !$escapeThisChar; // Escape the next character?
            } else {
                switch ($char) {
                    case '(':
                        if ($escapeThisChar) {
                            $replaceChar = true;
                        } else {
                            if ($inQuote) {
                                $replaceChar = true;
                            } else {
                                if ($braceDepth++ > 0) {
                                    $replaceChar = true; // Increment brace depth
                                }
                            }
                        }

                        break;
                    case ')':
                        if ($escapeThisChar) {
                            $replaceChar = true;
                        } else {
                            if ($inQuote) {
                                $replaceChar = true;
                            } else {
                                if (--$braceDepth > 0) {
                                    $replaceChar = true; // Decrement brace depth
                                }
                                if ($braceDepth < 0) {
                                    $braceDepth = 0;
                                }
                            }
                        }

                        break;
                    case '"':
                        if ($escapeThisChar) {
                            $replaceChar = true;
                        } else {
                            if ($braceDepth === 0) {
                                $inQuote = !$inQuote; // Are we inside a quoted string?
                            } else {
                                $replaceChar = true;
                            }
                        }

                        break;
                    case '.': // Dots don't help us either
                        if ($escapeThisChar) {
                            $replaceChar = true;
                        } else {
                            if ($braceDepth > 0)
                                $replaceChar = true;
                        }

                        break;
                    default:
                }

                $escapeThisChar = false;
                if ($replaceChar)
                    $email[$i] = 'x'; // Replace the offending character with something harmless
            }
        }

        $localPart = substr($email, 0, $atIndex);
        $domain = substr($email, $atIndex + 1);
        $FWS = "(?:(?:(?:[ \\t]*(?:\\r\\n))?[ \\t]+)|(?:[ \\t]+(?:(?:\\r\\n)[ \\t]+)*))"; // Folding white space
// Let's check the local part for RFC compliance...
//
        // local-part      =       dot-atom / quoted-string / obs-local-part
// obs-local-part  =       word *("." word)
// 	(http://tools.ietf.org/html/rfc5322#section-3.4.1)
//
        // Problem: need to distinguish between "first.last" and "first"."last"
// (i.e. one element or two). And I suck at regexes.
        $dotArray = /* . (array[int]string) . */ preg_split('/\\.(?=(?:[^\\"]*\\"[^\\"]*\\")*(?![^\\"]*\\"))/m', $localPart);
        $partLength = 0;

        foreach ($dotArray as $element) {
// Remove any leading or trailing FWS
            $element = preg_replace("/^$FWS|$FWS\$/", '', $element);

// Then we need to remove all valid comments (i.e. those at the start or end of the element
            $elementLength = strlen($element);

            if ($element[0] === '(') {
                $indexBrace = strpos($element, ')');
                if ($indexBrace !== false) {
                    if (preg_match('/(?<!\\\\)[\\(\\)]/', substr($element, 1, $indexBrace - 1)) > 0) {
                        return false; // Illegal characters in comment
                    }
                    $element = substr($element, $indexBrace + 1, $elementLength - $indexBrace - 1);
                    $elementLength = strlen($element);
                }
            }

            if ($element[$elementLength - 1] === ')') {
                $indexBrace = strrpos($element, '(');
                if ($indexBrace !== false) {
                    if (preg_match('/(?<!\\\\)(?:[\\(\\)])/', substr($element, $indexBrace + 1, $elementLength - $indexBrace - 2)) > 0) {
                        return false; // Illegal characters in comment
                    }
                    $element = substr($element, 0, $indexBrace);
                    $elementLength = strlen($element);
                }
            }

// Remove any leading or trailing FWS around the element (inside any comments)
            $element = preg_replace("/^$FWS|$FWS\$/", '', $element);

// What's left counts towards the maximum length for this part
            if ($partLength > 0)
                $partLength++; // for the dot
            $partLength += strlen($element);

// Each dot-delimited component can be an atom or a quoted string
// (because of the obs-local-part provision)
            if (preg_match('/^"(?:.)*"$/s', $element) > 0) {
// Quoted-string tests:
//
                // Remove any FWS
                $element = preg_replace("/(?<!\\\\)$FWS/", '', $element);
// My regex skillz aren't up to distinguishing between \" \\" \\\" \\\\" etc.
// So remove all \\ from the string first...
                $element = preg_replace('/\\\\\\\\/', ' ', $element);
                if (preg_match('/(?<!\\\\|^)["\\r\\n\\x00](?!$)|\\\\"$|""/', $element) > 0)
                    return false; // ", CR, LF and NUL must be escaped, "" is too short
            } else {
// Unquoted string tests:
//
                // Period (".") may...appear, but may not be used to start or end the
// local part, nor may two or more consecutive periods appear.
// 	(http://tools.ietf.org/html/rfc3696#section-3)
//
                // A zero-length element implies a period at the beginning or end of the
// local part, or two periods together. Either way it's not allowed.
                if ($element === '')
                    return false; // Dots in wrong place
// Any ASCII graphic (printing) character other than the
// at-sign ("@"), backslash, double quote, comma, or square brackets may
// appear without quoting.  If any of that list of excluded characters
// are to appear, they must be quoted
// 	(http://tools.ietf.org/html/rfc3696#section-3)
//
                // Any excluded characters? i.e. 0x00-0x20, (, ), <, >, [, ], :, ;, @, \, comma, period, "
                if (preg_match('/[\\x00-\\x20\\(\\)<>\\[\\]:;@\\\\,\\."]/', $element) > 0)
                    return false; // These characters must be in a quoted string
            }
        }

        if ($partLength > 64)
            return false; // Local part must be 64 characters or less

            
// Now let's check the domain part...
// The domain name can also be replaced by an IP address in square brackets
// 	(http://tools.ietf.org/html/rfc3696#section-3)
// 	(http://tools.ietf.org/html/rfc5321#section-4.1.3)
// 	(http://tools.ietf.org/html/rfc4291#section-2.2)
        if (preg_match('/^\\[(.)+]$/', $domain) === 1) {
// It's an address-literal
            $addressLiteral = substr($domain, 1, strlen($domain) - 2);
            $matchesIP = array();

// Extract IPv4 part from the end of the address-literal (if there is one)
            if (preg_match('/\\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.) {3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $addressLiteral, $matchesIP) > 0) {
                $index = strrpos($addressLiteral, $matchesIP[0]);

                if ($index === 0) {
// Nothing there except a valid IPv4 address, so...
                    return true;
                } else {
// Assume it's an attempt at a mixed address (IPv6 + IPv4)
                    if ($addressLiteral[$index - 1] !== ':')
                        return false; // Character preceding IPv4 address must be ':'
                    if (substr($addressLiteral, 0, 5) !== 'IPv6:')
                        return false; // RFC5321 section 4.1.3

                    $IPv6 = substr($addressLiteral, 5, ($index === 7) ? 2 : $index - 6);
                    $groupMax = 6;
                }
            } else {
// It must be an attempt at pure IPv6
                if (substr($addressLiteral, 0, 5) !== 'IPv6:')
                    return false; // RFC5321 section 4.1.3
                $IPv6 = substr($addressLiteral, 5);
                $groupMax = 8;
            }

            $groupCount = preg_match_all('/^[0-9a-fA-F]{0,4}|\\:[0-9a-fA-F]{0,4}|(.)/', $IPv6, $matchesIP);
            $index = strpos($IPv6, '::');

            if ($index === false) {
// We need exactly the right number of groups
                if ($groupCount !== $groupMax)
                    return false; // RFC5321 section 4.1.3
            } else {
                if ($index !== strrpos($IPv6, '::'))
                    return false; // More than one '::'
                $groupMax = ($index === 0 || $index === ( strlen($IPv6) - 2)) ? $groupMax : $groupMax - 1;
                if ($groupCount > $groupMax)
                    return false; // Too many IPv6 groups in address
            }

// Check for unmatched characters
            array_multisort($matchesIP[1], SORT_DESC);
            if ($matchesIP[1][0] !== '')
                return false; // Illegal characters in address
// It's a valid IPv6 address, so...
            return true;
        } else {
// It's a domain name...
// The syntax of a legal Internet host name was specified in RFC-952
// One aspect of host name syntax is hereby changed: the
// restriction on the first character is relaxed to allow either a
// letter or a digit.
// 	(http://tools.ietf.org/html/rfc1123#section-2.1)
//
            // NB RFC 1123 updates RFC 1035, but this is not currently apparent from reading RFC 1035.
//
            // Most common applications, including email and the Web, will generally not
// permit...escaped strings
// 	(http://tools.ietf.org/html/rfc3696#section-2)
//
            // the better strategy has now become to make the "at least one period" test,
// to verify LDH conformance (including verification that the apparent TLD name
// is not all-numeric)
// 	(http://tools.ietf.org/html/rfc3696#section-2)
//
            // Characters outside the set of alphabetic characters, digits, and hyphen MUST NOT appear in domain name
// labels for SMTP clients or servers
// 	(http://tools.ietf.org/html/rfc5321#section-4.1.2)
//
            // RFC5321 precludes the use of a trailing dot in a domain name for SMTP purposes
// 	(http://tools.ietf.org/html/rfc5321#section-4.1.2)
            $dotArray = /* . (array[int]string) . */ preg_split('/\\.(?=(?:[^\\"]*\\"[^\\"]*\\")*(?![^\\"]*\\"))/m', $domain);
            $partLength = 0;

            if (count($dotArray) === 1)
                return false; // Mail host can't be a TLD

            foreach ($dotArray as $element) {
// Remove any leading or trailing FWS
                $element = preg_replace("/^$FWS|$FWS\$/", '', $element);

// Then we need to remove all valid comments (i.e. those at the start or end of the element
                $elementLength = strlen($element);

                if ($element[0] === '(') {
                    $indexBrace = strpos($element, ')');
                    if ($indexBrace !== false) {
                        if (preg_match('/(?<!\\\\)[\\(\\)]/', substr($element, 1, $indexBrace - 1)) > 0) {
                            return false; // Illegal characters in comment
                        }
                        $element = substr($element, $indexBrace + 1, $elementLength - $indexBrace - 1);
                        $elementLength = strlen($element);
                    }
                }

                if ($element[$elementLength - 1] === ')') {
                    $indexBrace = strrpos($element, '(');
                    if ($indexBrace !== false) {
                        if (preg_match('/(?<!\\\\)(?:[\\(\\)])/', substr($element, $indexBrace + 1, $elementLength - $indexBrace - 2)) > 0) {
                            return false; // Illegal characters in comment
                        }
                        $element = substr($element, 0, $indexBrace);
                        $elementLength = strlen($element);
                    }
                }

// Remove any leading or trailing FWS around the element (inside any comments)
                $element = preg_replace("/^$FWS|$FWS\$/", '', $element);

// What's left counts towards the maximum length for this part
                if ($partLength > 0)
                    $partLength++; // for the dot
                $partLength += strlen($element);

// The DNS defines domain name syntax very generally -- a
// string of labels each containing up to 63 8-bit octets,
// separated by dots, and with a maximum total of 255
// octets.
// 	(http://tools.ietf.org/html/rfc1123#section-6.1.3.5)
                if ($elementLength > 63)
                    return false; // Label must be 63 characters or less
// Each dot-delimited component must be atext
// A zero-length element implies a period at the beginning or end of the
// local part, or two periods together. Either way it's not allowed.
                if ($elementLength === 0)
                    return false; // Dots in wrong place
// Any ASCII graphic (printing) character other than the
// at-sign ("@"), backslash, double quote, comma, or square brackets may
// appear without quoting.  If any of that list of excluded characters
// are to appear, they must be quoted
// 	(http://tools.ietf.org/html/rfc3696#section-3)
//
                // If the hyphen is used, it is not permitted to appear at
// either the beginning or end of a label.
// 	(http://tools.ietf.org/html/rfc3696#section-2)
//
                // Any excluded characters? i.e. 0x00-0x20, (, ), <, >, [, ], :, ;, @, \, comma, period, "
                if (preg_match('/[\\x00-\\x20\\(\\)<>\\[\\]:;@\\\\,\\."]|^-|-$/', $element) > 0) {
                    return false;
                }
            }

            if ($partLength > 255)
                return false; // Local part must be 64 characters or less

            if (preg_match('/^[0-9]+$/', $element) > 0)
                return false; // TLD can't be all-numeric

                
// Check DNS?
            if ($checkDNS && function_exists('checkdnsrr')) {
                if (!(checkdnsrr($domain, 'A') || checkdnsrr($domain, 'MX') )) {
                    return false; // Domain doesn't actually exist
                }
            }
        }

// Eliminate all other factors, and the one which remains must be the truth.
// 	(Sherlock Holmes, The Sign of Four)
        return true;
    }

    /**
     * Rekurzivně překóduje pole
     *
     * @param  string $in_charset
     * @param  string $out_charset
     * @param  array  $arr         originální pole
     * @return array  překódované pole
     */
    public function recursiveIconv($in_charset, $out_charset, $arr)
    {
        if (!is_array($arr)) {
            return iconv($in_charset, $out_charset, $arr);
        }
        $ret = $arr;
        array_walk_recursive($ret, array($this, "arrayIconv"), array($in_charset, $out_charset));

        return $ret;
    }

    /**
     * Pomocná funkce pro překódování vícerozměrného pole
     * @param mixed  $val
     * @param string $key
     * @param mixed  $userdata
     */
    public function arrayIconv(&$val, $key, $userdata)
    {
        $val = iconv($userdata[0], $userdata[1], $val);
    }

    /**
     * Zapíše zprávu do logu
     *
     * @param string $message zpráva
     * @param string $type    typ zprávy (info|warning|success|error|*)
     *
     * @return bool byl report zapsán ?
     */
    public function addToLog($message, $type = 'message')
    {
        if (is_object($this->logger)) {
            $this->logger->addToLog($this->getObjectName(), $message, $type);
        }
    }

    /**
     * Oznamuje chybovou událost
     *
     * @param string $message    zpráva
     * @param mixed  $objectData pole dat k zaznamenání
     */
    public function error($message, $objectData = null)
    {
        if (is_object($this->logger)) {
            $this->logger->error($this->getObjectName(), $message, $objectData = null);
        }
        $this->addStatusMessage($message,'error');
    }

    /**
     * Magická funkce pro všechny potomky
     *
     * @return string
     */
    public function __toString()
    {
        return 'Object: ' . $this->getObjectName();
    }

    /**
     * Pro serializaci připraví vše
     *
     * @return array
     */
    public function __sleep()
    {
        $objectVars = array_keys(get_object_vars($this));
        if (@method_exists(parent, '__sleep')) {
            $parentObjectVars = parent::__sleep();
            array_push($objectVars, $parentObjectVars);
        }
        $this->saveObjectIdentity();

        return $objectVars;
    }

    /**
     * Zobrazí velikost souboru v srozumitelném tvaru
     *
     * @param  int    $filesize bytů
     * @return string
     */
    static public function humanFilesize($filesize)
    {

        if (is_numeric($filesize)) {
            $decr = 1024;
            $step = 0;
            $prefix = array('Byte', 'KB', 'MB', 'GB', 'TB', 'PB');

            while (($filesize / $decr) > 0.9) {
                $filesize = $filesize / $decr;
                $step++;
            }

            return round($filesize, 2) . ' ' . $prefix[$step];
        } else {
            return 'NaN';
        }
    }

    /**
     * Akce po probuzení ze serializace
     */
    public function __wakeup()
    {
        $this->setObjectName();
        $this->restoreObjectIdentity();
    }

}
