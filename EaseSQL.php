<?php

/**
 * Abstraktní databázová třída
 *
 * @package EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@vitexsoftware.cz (G)
 */
require_once 'EaseBase.php';

/**
 * Virtuálni třída pro práci s databází
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseSQL extends EaseSand
{

    /**
     * SQL operation result handle
     * @var resource
     */
    public $Result = null;

    /**
     * SQL Handle
     * @var resource
     */
    public $SQLLink = null;
    /**
     * IP serveru
     * @var string
     */
    public $Server = null;

    /**
     * DB Login
     * @var string
     */
    public $Username = null;

    /**
     * DB heslo
     * @var string
     */
    public $Password = null;

    /**
     * Database to connect by default
     * @var string
     */
    public $Database = null;

    /**
     * Status připojení
     * @var bool
     */
    public $Status = null;
//Pripojeno ?
    /**
     * Hodnota posledního voloženeho AutoIncrement sloupečku
     * @var int unsigned
     */
    public $LastInsertID = null;

    /**
     * Poslední vykonaná SQL Query
     * @var string
     */
    public $LastQuery = '';

    /**
     * Počet ovlivněných nebo vrácených řádek při $this->LastQuery
     * @var string
     */
    public $NumRows = 0;

    /**
     * Pole obsahující informace o základních paramatrech SQL přiopojení
     * @var array
     */
    public $Report = array('LastMessage' => 'Please extend');

    /**
     * Klíčový sloupeček pro SQL operace
     * @var string
     */
    public $KeyColumn = '';

    /**
     * Název práve zpracovávané tabulky
     * @var string
     */
    public $TableName = '';

    /**
     * Pole obsahující strukturu SQL tabulky
     * @var array
     */
    public $TableStructure = array();

    /**
     * Poslední Chybová zpráva obdržená od SQL serveru
     * @var string
     */
    public $ErrorText = null;

    /**
     * Kod SQL chyby
     * @var int
     */
    public $ErrorNumber = null;

    /**
     * Pole obsahující výsledky posledního SQL příkazu
     * @var array
     */
    public $ResultArray = array();

    /**
     * Pomocná proměnná pro datové operace
     * @var array
     */
    public $Data = null;

    /**
     * Poslední zpráva obdžená od SQL serveru
     * @var string
     */
    public $LastMessage = null;

    /**
     * Prodlevy v sekundách pro znovupřipojení k databázi
     * @var array
     */
    public $ReconectTimeouts = array('web' => 1, 'cgi' => 10);

    /**
     * Nastavení vlastností přípojení
     * @var array
     */
    public $ConnectionSettings = array();

    /**
     * Indikátor nastavení připojení - byly vykonány SET příkazy
     * @var boolean
     */
    protected $connectAllreadyUP = false;

    /**
     * Obecný objekt databáze
     */
    public function __construct()
    {
        parent::__construct();
        if (!isset($this->Server) && defined('DB_SERVER')) {
            $this->Server = constant('DB_SERVER');
        }
        if (!isset($this->Username) && defined('DB_SERVER_USERNAME')) {
            $this->Username = constant('DB_SERVER_USERNAME');
        }
        if (!isset($this->Password) && defined('DB_SERVER_PASSWORD')) {
            $this->Password = constant('DB_SERVER_PASSWORD');
        }
        if (!isset($this->Database) && defined('DB_DATABASE')) {
            $this->Database = constant('DB_DATABASE');
        }
        $this->connect();
    }

    /**
     * Připojení k databázi
     */
    public function connect()
    {
        $this->setUp();
        $this->Status = true;
    }

    /**
     * Přepene databázi
     *
     * @param  type    $DBName
     * @return boolean
     */
    public function selectDB($DBName = null)
    {
        if (!is_null($DBName)) {
            $this->Database = $DBName;
        }

        return null;
    }

    /**
     * Id vrácené po INSERTu
     *
     * @return int
     */
    public function getInsertID()
    {
        return $this->LastInsertID;
    }

    /**
     * Otestuje moznost pripojeni k sql serveru
     *
     * @param boolean $succes vynucený výsledek
     *
     * @return $Success
     */
    public function ping($succes = null)
    {
        return $succes;
    }

    /**
     * Po deserializaci se znovu připojí
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $this->connect();
    }

    /**
     * Odstraní z SQL dotazu "nebezpečné" znaky
     *
     * @param string $QueryRaw SQL Query
     *
     * @return string SQL Query
     */
    public function sanitizeQuery($QueryRaw)
    {
        $SanitizedQuery = trim($QueryRaw);

        return $SanitizedQuery;
    }

    public function makeReport()
    {
        $this->Report['LastMessage'] = $this->LastMessage;
        $this->Report['ErrorText'] = $this->ErrorText;
        $this->Report['Database'] = $this->Database;
        $this->Report['Username'] = $this->Username;
        $this->Report['Server'] = $this->Server;
    }

    /**
     * Nastaví připojení
     */
    public function setUp()
    {
        if (!$this->connectAllreadyUP) {
            if (isset($this->ConnectionSettings) && is_array($this->ConnectionSettings) && count($this->ConnectionSettings)) {
                foreach ($this->ConnectionSettings as $SetName => $SetValue) {
                    if (strlen($SetName)) {
                        $this->exeQuery("SET $SetName $SetValue");
                    }
                }
                $this->connectAllreadyUP = true;
            }
        }
    }

    public function setTable($TableName)
    {
        $this->TableName = $TableName;
    }

    /**
     * Otestuje všechny náležitosti pro vytvoření tabulky
     *
     * @param  array   $TableStructure
     * @param  string  $TableName
     * @return boolean
     */
    public function createTableQuery(&$TableStructure, $TableName = null)
    {
        if (!$TableStructure) {
            $TableStructure = $this->TableStructure;
        }
        if (!is_array($TableStructure)) {
            $this->error('Missing table structure for creating TableCreate QueryRaw');

            return false;
        }
        if (!$TableName) {
            $TableName = $this->TableName;
        }
        if (!$TableName) {
            $this->error('Missing table name for creating TableCreate QueryRaw');

            return false;
        }

        return true;
    }

    /**
     * Opraví délky políček u nichž je překročena délka
     *
     * @param array $Data asociativní pole dat
     *
     * @return array
     */
    protected function fixColumnsLength($Data)
    {
        foreach ($this->TableStructure as $Column => $ColumnProperties) {
            if (array_key_exists($Column, $this->TableStructure)) {
                $Regs = array();
                if (@ereg("(.*)\((.*)\)", $ColumnProperties['type'], $Regs)) {
                    list(, $Type, $Size) = $Regs;
                    switch ($Type) {
                        case 'varchar':
                        case 'string':
                            if (array_key_exists($Column, $Data) && $Size) {
                                if (strlen($Data[$Column]) > $Size) {
                                    $this->addToLog('Column ' . $this->TableName . '.' . $Column . ' content truncated: ' . substr($Data[$Column], $Size - strlen($Data[$Column])), 'warning');
                                    $Data[$Column] = substr($Data[$Column], 0, $Size - 1) . '_';
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        return $Data;
    }

    /**
     * Zkontroluje předpoklady pro vytvoření tabulky ze struktury
     *
     * @param array  $TableStructure struktura tabulky
     * @param string $TableName      název tabulky
     *
     * @return boolean Success
     */
    public function createTable(&$TableStructure = null, $TableName = null)
    {
        if (!$TableName) {
            $TableName = $this->TableName;
        }
        if (!$TableStructure) {
            $TableStructure = $this->TableStructure;
        }

        if (!$TableStructure) {
            $TableStructure = $this->TableStructure;
        }

        if (!strlen($TableName)) {
            $this->error('Missing table name for table creating');

            return false;
        }
        if (!is_array($TableStructure)) {
            $this->error('Missing table structure for table creating');

            return false;
        }

        return true;
    }

    /**
     * Vrací počet řádků vrácených nebo ovlivněným posledním sql dotazem.
     *
     * @return int počet řádků
     */
    public function getNumRows()
    {
        return $this->NumRows;
    }

    /**
     * Poslední vykonaný dotaz
     *
     * @return int počet řádků
     */
    public function getLastQuery()
    {
        return $this->LastQuery;
    }

    /**
     * Poslední genrované ID
     *
     * @return int ID
     */
    public function getLastInsertID()
    {
        return $this->LastInsertID;
    }

    /**
     * Vrací chybovou zprávu SQL
     *
     * @return string
     */
    public function getLastError()
    {
        if ($this->ErrorText) {
            if (isset($this->ErrorNumber)) {
                return '#' . $this->ErrorNumber . ': ' . $this->ErrorText;
            } else {
                return $this->ErrorText;
            }
        } else {
            return null;
        }
    }

    /**
     * Vrací strukturu SQL tabulky jako pole
     *
     * @param  string       $TableName
     * @return null|boolean
     */
    public function describe($TableName = null)
    {
        if (!$TableName) {
            $TableName = $this->TableName;
        }
        if (!$this->tableExist($TableName)) {
            $this->addToLog('Try to describe nonexistent table: ' . $TableName, 'waring');

            return null;
        }

        return true;
    }

    /**
     * Ověří existenci tabulky
     *
     * @param  string       $TableName
     * @return null|boolean
     */
    public function tableExist($TableName = null)
    {
        if (!$TableName)
            $TableName = $this->TableName;
        if (!$TableName) {
            $this->error('TableExist: $TableName not known');

            return null;
        }

        return true;
    }

    /**
     * Zaznamená SQL Chybu
     *
     * @param string $Title volitelný popisek, většinou název volající funkce
     */
    public function logError($Title = null)
    {
        if (is_null($Title)) {
            list(, $Caller) = debug_backtrace(false);
            $Title = $Caller['function'];
        }
        if (isset($this->EaseShared->User) && is_object($this->EaseShared->User)) {
            return $this->EaseShared->User->addStatusMessage($Title . ': #' . $this->ErrorNumber . ' ' . $this->ErrorText, 'error');
        } else {
            return $this->addToLog($Title . ': #' . $this->ErrorNumber . ' ' . $this->ErrorText, 'error');
        }
    }

    /**
     * Znovu se připojí k databázi
     */
    public function reconnect()
    {
        $this->close();
        sleep($this->ReconectTimeouts[$this->EaseShared->RunType]);
        $this->connect();
    }

    /**
     * Při serializaci vynuluje poslední Query
     *
     * @return boolean
     */
    public function __sleep()
    {
        $this->LastQuery = null;

        return parent::__sleep();
    }

    /**
     * Zavře databázové spojení
     */
    public function __destruct()
    {
        $this->Close();
    }

    /**
     * Vrací výsledek dotazu jako dvourozměrné pole
     *
     * @param string $QueryRaw SQL příkaz
     *
     * @return array|null
     */
    public function queryTo2DArray($QueryRaw)
    {
        $Result = $this->queryToArray($QueryRaw);
        if (count($Result)) {
            $Values = array();
            foreach ($Result as $Value) {
                $Values[] = current($Value);
            }

            return $Values;
        }

        return $Result;
    }

    /**
     * Vrací první položku výsledku dotazu
     *
     * @param string $QueryRaw SQL příkaz vracející jednu hodnotu
     *
     * @return string|null
     */
    public function queryToValue($QueryRaw)
    {
        $Result = $this->queryToArray($QueryRaw);
        if (count($Result)) {
            return current(current($Result));
        } else {
            return null;
        }
    }

    /**
     * Vrací databázový objekt Pear::DB
     *
     * @link http://pear.php.net/manual/en/package.database.mdb2.php
     * @todo SET,mdb2
     *
     * @return DB|null objekt databáze
     */
    public static function & getPearObject()
    {
        require_once 'DB.php';
        $DbHelper = new DB;

        $dsn = array(
            'phptype' => 'mysql', //TODO - pořešit v EaseMySQL
            'username' => DB_SERVER_USERNAME,
            'password' => DB_SERVER_PASSWORD,
            'hostspec' => DB_SERVER
        );

        $db = & $DbHelper->connect($dsn);

        if (PEAR::isError($db)) {
            return null;
        }

        $db->query('USE ' . DB_DATABASE);

        return $db;
    }

}
