<?php

/**
 * Zakladni objekt urceny k rodicovstvi vsem pouzivanym objektum
 * 
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G) 
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
     * ID jazyka - přebírá se z OSCommerce
     * @var int
     */
    public $languages_id = 4;

    /**
     * Nazev jazyka
     * @var string
     */
    public $Language = null;

    /**
     * Nazev jazyka
     * @var string
     */
    public $LangCode = null;

    /**
     * Data držená objektem
     * @var array
     */
    public $Data = null;

    /**
     * Výchozí index pole pro držení dat
     * @var type 
     */
    public $DefaultDataPrefix = null;

    /**
     * Obsahuje všechna pole souhrně považovaná za identitu. Toto pole je plněno
     * v metodě SaveObjectIdentity {volá se automaticky v EaseSand::__construct()}
     * @var array
     */
    public $Identity = array();

    /**
     * Původní identita sloužící jako záloha k zrekonstruování počátečního stavu objektu.
     * @var array
     */
    public $InitialIdenty = array();

    /**
     * Tyto sloupecky jsou uchovavany pri operacich s identitou objektu
     * @var array
     */
    public $IdentityColumns = array('ObjectName',
        'MyKeyColumn', 'MSKeyColumn',
        'MyTable', 'MSTable',
        'MyIDSColumn', 'MSIDSColumn',
        'MyRefIDColumn', 'MSRefIDColumn',
        'MyCreateColumn', 'MSCreateColumn',
        'MyLastModifiedColumn', 'MSLastModifiedColumn');

    /**
     * Klíčový sloupeček v používané MSSQL tabulce
     * @var string
     */
    public $MSKeyColumn = 'ID';

    /**
     * Klíčový sloupeček v používané MySQL tabulce
     * @var string
     */
    public $MyKeyColumn = 'id';

    /**
     * Synchronizační sloupeček. napr products_ids
     * @var string
     */
    public $MyIDSColumn = null;

    /**
     * Synchronizační sloupeček. napr IDS
     * @var string
     */
    public $MSIDSColumn = null;

    /**
     * Synchronizační sloupeček. napr products_MSSQL_id
     * @var string
     */
    public $MyRefIDColumn = null;

    /**
     * Synchronizační sloupeček. napr
     * @var string
     */
    public $MSRefIDColumn = null;

    /**
     * Sloupeček obsahující datum vložení záznamu do shopu
     * @var string
     */
    public $MyCreateColumn = null;

    /**
     *  Sloupeček obsahujíci datum vložení záznamu do Pohody
     * @var string
     */
    public $MSCreateColumn = null;

    /**
     * Slopecek obsahujici datum poslení modifikace záznamu do shopu
     * @var string
     */
    public $MyLastModifiedColumn = null;

    /**
     * Slopecek obsahujici datum poslení modifikace záznamu do Pohody
     * @var string
     */
    public $MSLastModifiedColumn = null;

    /**
     * Objekt pro logování
     * @var EaseLogger
     */
    public $Logger = null;

    /**
     * Jakým objektem řešit logování ?
     * @var EaseLogger
     */
    public $LogType = 'EaseLogger';

    /**
     * Odkaz na vlastnící objekt
     * @var EaseSand|mixed object
     */
    public $ParentObject = null;

    /**
     * Sdílený objekt frameworku
     * 
     * @deprecated since version 1
     * @var EaseShared 
     */
    public $EaseShared = null;

    /**
     * Sdílený objekt frameworku
     * @var EaseShared 
     */
    public $easeShared = null;
    
    /**
     * Prapředek všech objektů
     */
    function __construct()
    {
        $this->EaseShared = EaseShared::singleton();
        if ($this->LogType != 'none') {
            $this->Logger = EaseLogger::singleton();
        }
        $this->setObjectName();
        $this->InitialIdenty = $this->saveObjectIdentity();
    }

    /**
     * Přidá zprávu do sdíleného zásobníku pro zobrazení uživateli
     * 
     * @param string  $Message  Text zprávy
     * @param string  $Type     Fronta zpráv (warning|info|error|success)
     * @param boolean $AddIcons přidá UTF8 ikonky na začátek zpráv
     * @param boolean $AddToLog zapisovat zpravu do logu ?
     * 
     * @return 
     */
    function addStatusMessage($Message, $Type = 'info', $AddIcons = true, $AddToLog = true)
    {
        return EaseShared::instanced()->addStatusMessage($Message, $Type, $AddIcons, $AddToLog);
    }

    /**
     * Připojí ke stávajícímu objektu přiřazený objekt
     * 
     * @param string $PropertyName název proměnné
     * @param object $Object       přiřazovaný objekt
     */
    public function attachObject($PropertyName, $Object)
    {
        if (is_object($Object)) {
            $this->$PropertyName = & $Object;
        }
    }

    public function xAttachObject($VariableName, $ObjectName, $ObjectParams = null)
    {

        if (!is_null($this->$VariableName)) {
            if (!is_object($this->$VariableName)) {
                die('AttachObject: UndefinedProperty $this->' . $VariableName . ' of ' . $this->getObjectName());
            }
        } else {

            if (class_exists($ObjectName)) {

                if (property_exists($this, 'ParentObject') && is_object($this->ParentObject)) {
                    //Pokud již rodičovský objekt obsahuje požadovanou vlastnost, pouze na ní odkážeme
                    if (property_exists($this->ParentObject, $VariableName)) {
                        $this->$VariableName = & $this->ParentObject->$VariableName;
                    }
                } else {
                    //jinak se vytvoří nová instance objektu
                    $NumberOfArgs = func_num_args();
                    if ($NumberOfArgs == 2) {
                        $this->$VariableName = new $ObjectName();
                    } else {
                        $Arguments = func_get_args();
                        array_shift($Arguments);
                        array_shift($Arguments);
                        eval('$this->' . $VariableName . ' = new ' . $ObjectName . '(' . implode(',', $Arguments) . ');');
                    }
                }
            }

            return true;
        }

        if (property_exists($this->$VariableName, 'ParentObject')) {
            $this->$VariableName->ParentObject = & $this;
        }
    }

    /**
     * Nastaví jméno objektu
     * 
     * @param string $ObjectName
     * 
     * @return string Jméno objektu
     */
    function setObjectName($ObjectName = null)
    {
        if ($ObjectName) {
            $this->ObjectName = $ObjectName;
        } else {
            $this->ObjectName = get_class($this);
        }
        return $this->ObjectName;
    }

    /**
     * Vrací jméno objektu
     * 
     * @return string
     */
    function getObjectName()
    {
        return $this->ObjectName;
    }

    /**
     * Nastaví novou identitu objektu
     * 
     * @param array $NewIdentity
     */
    function setObjectIdentity($NewIdentity)
    {
        $Changes = 0;
        $this->saveObjectIdentity();
        foreach ($this->IdentityColumns as $Column) {
            if (isset($NewIdentity[$Column])) {
                $this->$Column = $NewIdentity[$Column];
                $Changes++;
            }
        }
        return $Changes;
    }

    /**
     * Uloží identitu objektu do pole $this->Identity
     * 
     * @return array pole s identitou
     */
    function saveObjectIdentity()
    {
        foreach ($this->IdentityColumns as $Column) {
            if (isset($this->$Column)) {
                $this->Identity[$Column] = $this->$Column;
            }
        }
        return $this->Identity;
    }

    /**
     * Obnoví uloženou identitu objektu
     * 
     * @param array $Identity pole s identitou např. array('MyTable'=>'user');
     */
    function restoreObjectIdentity($Identity = null)
    {
        foreach ($this->IdentityColumns as $Column)
            if (isset($this->Identity[$Column]))
                $this->$Column = $this->Identity[$Column];
    }

    /**
     * Obnoví poslední použitou identitu 
     */
    function resetObjectIdentity()
    {
        $this->Identity = $this->InitialIdenty;
        $this->restoreObjectIdentity();
    }

    /**
     * Z datového pole $SourceArray přemístí políčko $ColumName do pole 
     * $DestinationArray
     * 
     * @param array  $SourceArray      zdrojové pole dat
     * @param array  $DestinationArray cílové pole dat
     * @param string $ColumName        název položky k převzetí 
     */
    static public function divDataArray(& $SourceArray, & $DestinationArray, $ColumName)
    {
        if (array_key_exists($ColumName, $SourceArray)) {
            $DestinationArray[$ColumName] = $SourceArray[$ColumName];
            unset($SourceArray[$ColumName]);
            return true;
        }
        return false;
    }

    /**
     * Vynuluje všechny pole vlastností objektu
     * 
     * @param array $DataPrefix název datové skupiny
     */
    function dataReset($DataPrefix = null)
    {
        if (!$DataPrefix) {
            $DataPrefix = $this->DefaultDataPrefix;
        }
        if ($DataPrefix) {
            $this->Data[$DataPrefix] = array();
        } else {
            $this->Data = array();
        }
    }

    /**
     * Načte $Data do polí objektu
     * 
     * @param array  $Data       asociativní pole dat
     * @param string $DataPrefix prefix skupiny dat (např. "MSSQL")
     * @param bool   $Reset      vyprazdnit pole před naplněním ?
     * 
     * @return int počet načtených položek
     */
    function setData($Data, $DataPrefix = null, $Reset = false)
    {
        if (is_null($Data) || !count($Data)) {
            return null;
        }
        if (!$DataPrefix) {
            $DataPrefix = $this->DefaultDataPrefix;
        }
        if ($Reset) {
            $this->dataReset($DataPrefix);
        }
        if ($DataPrefix) {
            if (isset($this->Data[$DataPrefix]) && is_array($this->Data[$DataPrefix])) {
                $this->Data[$DataPrefix] = array_merge($this->Data[$DataPrefix], $Data);
            } else {
                $this->Data[$DataPrefix] = $Data;
            }
        } else {
            if (is_array($this->Data)) {
                $this->Data = array_merge($this->Data, $Data);
            } else {
                $this->Data = $Data;
            }
        }
        return count($Data);
    }

    /**
     * Vrací celé pole dat objektu
     * 
     * @param string $DataPrefix
     * 
     * @return array 
     */
    function getData($DataPrefix = null)
    {
        if (is_null($DataPrefix)) {
            $DataPrefix = $this->DefaultDataPrefix;
        }
        if ($DataPrefix) {
            if (isset($this->Data[$DataPrefix])) {
                return $this->Data[$DataPrefix];
            }
            return null;
        } else {
            return $this->Data;
        }
    }

    /**
     * Vrací počet položek dat objektu
     * 
     * @param string $DataPrefix
     * 
     * @return int 
     */
    function getDataCount($DataPrefix = null)
    {
        $Counter = 0;
        if (!$DataPrefix) {
            $DataPrefix = $this->DefaultDataPrefix;
        }
        if ($DataPrefix) {
            return count($this->Data[$DataPrefix]);
        } else {
            return count($this->Data);
        }
    }

    /**
     * Vrací hodnotu z pole dat pro MySQL
     * 
     * @param string $ColumnName název hodnoty/sloupečku
     * @param string $DataPrefix 
     * 
     * @return mixed
     */
    function getDataValue($ColumnName, $DataPrefix = null)
    {
        if (is_null($DataPrefix)) {
            $DataPrefix = $this->DefaultDataPrefix;
        }
        if ($DataPrefix) {
            if (isset($this->Data[$DataPrefix]) && isset($this->Data[$DataPrefix][$ColumnName])) {
                return $this->Data[$DataPrefix][$ColumnName];
            }
        } else {
            if (isset($this->Data[$ColumnName])) {
                return $this->Data[$ColumnName];
            }
        }
        return null;
    }

    /**
     * Nastaví hodnotu poli objektu
     * 
     * @param string $ColumnName název datové kolonky
     * @param mixed  $Value      hodnota dat
     * @param string $DataPrefix prefix skupiny dat
     * 
     * @return boolean Success
     */
    public function setDataValue($ColumnName, $Value, $DataPrefix = null)
    {
        if (!$DataPrefix) {
            $DataPrefix = $this->DefaultDataPrefix;
        }
        if ($DataPrefix) {
            $this->Data[$DataPrefix][$ColumnName] = $Value;
        } else {
            $this->Data[$ColumnName] = $Value;
        }
        return true;
    }

    /**
     * Odstrani polozku z pole dat pro MySQL
     * 
     * @param string $ColumnName název klíče k vymazání
     * @param string $DataPrefix prefix skupiny dat
     * 
     * @return boolean success
     */
    function unsetDataValue($ColumnName, $DataPrefix = null)
    {
        if (!$DataPrefix) {
            $DataPrefix = $this->DefaultDataPrefix;
        }
        if ($DataPrefix) {
            if (isset($this->Data[$DataPrefix][$ColumnName])) {
                unset($this->Data[$DataPrefix][$ColumnName]);
                return true;
            }
        }
        if (isset($this->Data[$ColumnName])) {
            unset($this->Data[$ColumnName]);
            return true;
        }
        return false;
    }

    /**
     * Převezme data do aktuálního pole dat
     * 
     * @param array  $Data       asociativní pole dat
     * @param string $DataPrefix prefix datové skupiny
     * 
     * @return int
     */
    function takeData($Data, $DataPrefix = null)
    {
        if (!$DataPrefix) {
            $DataPrefix = $this->DefaultDataPrefix;
        }
        if ($DataPrefix) {
            if (isset($this->Data[$DataPrefix]) && is_array($this->Data[$DataPrefix])) {
                $this->Data[$DataPrefix] = array_merge($this->Data[$DataPrefix], $Data);
            } else {
                $this->Data[$DataPrefix] = $Data;
            }
        } else {
            if (is_array($this->Data)) {
                $this->Data = array_merge($this->Data, $Data);
            } else {
                $this->Data = $Data;
            }
        }
        return count($Data);
    }

    /**
     * Funkce pro defaultní slashování v celém frameworku
     * 
     * @param string $Text 
     * 
     * @return string
     */
    public function easeAddSlashes($Text)
    {
        return addSlashes($Text);
    }

    /**
     * Zobrazí obsah pole nebo objektu
     * 
     * @param mixed  $Argument All used by print_r() function
     * @param string $Comment  hint při najetí myší
     */
    public function printPre($Argument, $Comment = '')
    {
        $RetVal = '';
        $ItemsCount = 0;
        if (is_object($Argument)) {
            $ItemsCount = count($Argument);
            $Comment = gettype($Argument) . ': ' . get_class($Argument) . ': ' . $Comment;
        } else {
            $Comment = gettype($Argument) . ': ' . $Comment;

            if (is_array($Argument)) {
                $ItemsCount = count($Argument);
            }
        }

        if ($ItemsCount) {
            $Comment .= " ($ItemsCount)";
        }

        if ($this->EaseShared->RunType == 'web') {
            $RetVal .= '<pre class="debug" style="overflow: scroll; border: solid 1px green;  color: white; background-color: black;" title="' . $Comment . '">';
        } else {
            $RetVal .= "\n########### $Comment ###########\n";
        }

        $RetVal .= $this->PrintPreBasic($Argument);

        if ($this->EaseShared->RunType == 'web') {
            $RetVal .= '</pre>';
        } else {
            $RetVal .= "\n";
        }
        return $RetVal;
    }

    /**
     * Vrací print_pre($Argument) jako řetězec
     * 
     * @param mixed $Argument
     * 
     * @return string
     */
    static public function printPreBasic($Argument)
    {
        return print_r($Argument, true);
    }

    /**
     * Vrací utf8 podřetězec
     * 
     * @param string $str    utf8
     * @param int    $String offset
     * @param int    $Length length
     * 
     * @return string utf8 
     */
    static public function substrUnicode($str, $String, $Length = null)
    {
        return join("", array_slice(preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $String, $Length));
    }

    /**
     * Odstraní z textu diakritiku
     * 
     * @param string $Text
     */
    static public function rip($Text)
    {
        $ConvertTable = Array(
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
        return @iconv('UTF-8', 'ASCII//TRANSLIT', strtr($Text, $ConvertTable));
    }

    /**
     * Šifrování
     * 
     * @param string $TextToEncrypt plaintext
     * @param string $EncryptKey    klíč
     * 
     * @return string encrypted text
     */
    static public function easeEncrypt($TextToEncrypt, $EncryptKey)
    {
        srand((double) microtime() * 1000000); //for sake of MCRYPT_RAND
        $EncryptKey = md5($EncryptKey);
        $EncryptHandle = mcrypt_module_open('des', '', 'cfb', '');
        $EncryptKey = substr($EncryptKey, 0, mcrypt_enc_get_key_size($EncryptHandle));
        $InitialVectorSize = mcrypt_enc_get_iv_size($EncryptHandle);
        $InitialVector = mcrypt_create_iv($InitialVectorSize, MCRYPT_RAND);
        if (mcrypt_generic_init($EncryptHandle, $EncryptKey, $InitialVector) != - 1) {
            $EncryptedText = mcrypt_generic($EncryptHandle, $TextToEncrypt);
            mcrypt_generic_deinit($EncryptHandle);
            mcrypt_module_close($EncryptHandle);
            $EncryptedText = $InitialVector . $EncryptedText;
            return $EncryptedText;
        }
    }

    /**
     * Dešifrování
     * 
     * @param string $TextToDecrypt šifrovaný text
     * @param string $EncryptKey    šifrovací klíč
     * 
     * @return string
     */
    public static function easeDecrypt(string $TextToDecrypt, $EncryptKey)
    {
        $EncryptKey = md5($EncryptKey);
        $EncryptHandle = mcrypt_module_open('des', '', 'cfb', '');
        $EncryptKey = substr($EncryptKey, 0, mcrypt_enc_get_key_size($EncryptHandle));
        $InitialVectorSize = mcrypt_enc_get_iv_size($EncryptHandle);
        $InitialVector = substr($TextToDecrypt, 0, $InitialVectorSize);
        $TextToDecrypt = substr($TextToDecrypt, $InitialVectorSize);
        if (mcrypt_generic_init($EncryptHandle, $EncryptKey, $InitialVector) != - 1) {
            $DecryptedText = mdecrypt_generic($EncryptHandle, $TextToDecrypt);
            mcrypt_generic_deinit($EncryptHandle);
            mcrypt_module_close($EncryptHandle);
            return $DecryptedText;
        }
    }

    /**
     * Generování náhodného čísla
     * 
     * @param int $Minimal
     * @param int $Maximal
     * 
     * @return float
     */
    static public function randomNumber($Minimal = null, $Maximal = null)
    {
        mt_srand((double) microtime() * 1000000);
        if (isset($Minimal) && isset($Maximal)) {
            if ($Minimal >= $Maximal) {
                return $Minimal;
            } else {
                return mt_rand($Minimal, $Maximal);
            }
        } else {
            return mt_rand();
        }
    }

    /**
     * Vrací náhodný řetězec dané délky
     * 
     * @param int $Length
     * 
     * @return string
     */
    static public function randomString($Length = 6)
    {
        return substr(str_replace("/", "A", str_replace(".", "X", crypt(strval(self::randomNumber(1000, 9000))))), 3, $Length);
    }

    /**
     * Oveření mailu
     * 
     * @param string $Email    mailová adresa
     * @param bool   $checkDNS testovat DNS ?
     * 
     * @package	  isemail
     * @author	  Dominic Sayers <dominic_sayers@hotmail.com>
     * @copyright 2009 Dominic Sayers
     * @license	  http://www.opensource.org/licenses/cpal_1.0 Common Public Attribution License Version 1.0 (CPAL) license
     * @link	  http://www.dominicsayers.com/isemail
     * @version	  1.9 - Minor modifications to make it compatible with PHPLint
     */
    function isEmail($Email, $checkDNS = false)
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
        $emailLength = strlen($Email);
        if ($emailLength > 256)
            return false; // Too long
        /*
          // Contemporary email addresses consist of a "local part" separated from
          // a "domain part" (a fully-qualified domain name) by an at-sign ("@").
          // 	(http://tools.ietf.org/html/rfc3696#section-3)
         */
        $atIndex = strrpos($Email, '@');

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
            $char = $Email[$i];
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
                    $Email[$i] = 'x'; // Replace the offending character with something harmless
            }
        }

        $localPart = substr($Email, 0, $atIndex);
        $domain = substr($Email, $atIndex + 1);
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
     * @param string $in_charset
     * @param string $out_charset
     * @param array $arr originální pole
     * @return array překódované pole
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
     * @param mixed $val
     * @param string $key
     * @param mixed $userdata
     */
    function arrayIconv(&$val, $key, $userdata)
    {
        $val = iconv($userdata[0], $userdata[1], $val);
    }

    /**
     * Zapíše zprávu do logu
     * 
     * @param string $Message zpráva
     * @param string $Type    typ zprávy (info|warning|success|error|*)
     * 
     * @return bool byl report zapsán ?
     */
    function addToLog($Message, $Type = 'message')
    {
        if (is_object($this->Logger)) {
            $this->Logger->addToLog($this->getObjectName(), $Message, $Type);
        }
    }

    /**
     * Oznamuje chybovou událost
     * 
     * @param string $Message    zpráva
     * @param mixed  $ObjectData pole dat k zaznamenání
     */
    function error($Message, $ObjectData = null)
    {
        if (is_object($this->Logger)) {
            $this->Logger->error($this->getObjectName(), $Message, $ObjectData = null);
        }
    }

    /**
     * Magická funkce pro všechny potomky
     * 
     * @return string
     */
    function __toString()
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
        $ObjectVars = array_keys(get_object_vars($this));
        if (@method_exists(parent, '__sleep')) {
            $ParentObjectVars = parent::__sleep();
            array_push($ObjectVars, $ParentObjectVars);
        }
        $this->saveObjectIdentity();
        return $ObjectVars;
    }

    /**
     * Akce po probuzení ze serializace
     */
    function __wakeup()
    {
        $this->setObjectName();
        $this->restoreObjectIdentity();
    }

}

?>