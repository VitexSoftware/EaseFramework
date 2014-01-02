<?php

/**
 * Podpora MSSQL databáze
 *
 * @category  Sql
 * @package   EaseFrameWork
 * @author    Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright 2009-2011 Vitex@hippy.cz (G)
 */
require_once 'EaseSQL.php';

/**
 * Basic Database Layer
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseDbMSSQL extends EaseSql
{

    public $Debug = false;
    public $NumRows = 0;
    public $LastInsertID = 0;
    public $LastQuery = '';
    public $Result = null;
    public $ResultArray = null;
    public $myDbLink = null;
    public $Lockfile = 'mssql-offline';

    /**
     * Hack pro svéhlavé FreeTDS na windows co ignoruje konfiguraci
     * @var boolean
     */
    public $WinToUtfRecode = false;

    /**
     * Kolikrát se pokusit připojit před offline
     * @var type
     */
    public $ConnectAttempts = 10;

    /**
     * Indikator skutečného připojení k MSSQL
     * @var boolean
     */
    public $Connected = false;

    /**
     * Nastavení vlastností přípojení
     * @var array
     */
    public $ConnectionSettings = array(
        'ANSI_NULLS' => 'ON',
        'QUOTED_IDENTIFIER' => 'ON',
        'CONCAT_NULL_YIELDS_NULL' => 'ON',
        'ANSI_WARNINGS' => 'ON',
        'ANSI_PADDING' => 'ON');

    /**
     * Saves obejct instace (singleton...)
     */
    private static $_instance = null;

    /**
     * Kolikáty pokus o připojení ?
     * @var int
     */
    public $instanceCounter = 0;

    /**
     * MSSQL mode
     * @var string online|offline
     */
    public $Mode = 'online'; // 'online' | 'offline'

    /**
     * Database layer
     *
     * @param type $Mode
     */

    public function __construct($Mode = 'online')
    {
        if (defined('FREETDS_RECODE')) {
            $this->WinToUtfRecode = constant('FREETDS_RECODE');
        }

        if (!isset($this->Server) && defined('MS_DB_SERVER')) {
            $this->Server = constant('MS_DB_SERVER');
        }
        if (!isset($this->Username) && defined('MS_DB_SERVER_USERNAME')) {
            $this->Username = constant('MS_DB_SERVER_USERNAME');
        }
        if (!isset($this->Password) && defined('MS_DB_SERVER_PASSWORD')) {
            $this->Password = constant('MS_DB_SERVER_PASSWORD');
        }
        if (!isset($this->Database) && defined('MS_DB_DATABASE')) {
            $this->Database = constant('MS_DB_DATABASE');
        }

        parent::__construct();
    }

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako
     * konstruktor) se bude v ramci behu programu pouzivat pouze jedna jeho
     * instance (ta prvni).
     *
     * @param string $Mode online|offline - zinicializuje bez připojení k MSSQL
     *
     * @link http://docs.php.net/en/language.oop5.patterns.html Dokumentace a priklad
     */
    public static function singleton($Mode = 'online')
    {
        if (!isset(self::$_instance)) {
            $ClassName = __CLASS__;
            self::$_instance = new $ClassName($Mode);
        }

        return self::$_instance;
    }

    /**
     * Připojí se k MSSQL
     *
     * @return boolean
     */
    public function connect()
    {
        if (is_null($this->SQLLink)) {
            if (++$this->instanceCounter > 1) {
                return null;
            }
        }
        if (!function_exists('mssql_connect')) {
            $this->error('ConnectReal: MSSQL is not compiled in');
            $this->Status = 'error';
            $this->LastMessage = 'MSSQL is not compiled in';
            $this->makeReport();

            return null;
        }
        $this->SQLLink = mssql_connect($this->Server, $this->Username, $this->Password);
        if ($this->SQLLink) {
            $this->Connected = true;
            mssql_min_error_severity(2);
            $this->selectDB($this->Database);
            parent::connect();

            return $this->Status;
        } else {
            $this->LastMessage = mssql_get_last_message();
            $this->addStatusMessage('connect: ' . $this->LastMessage, 'warning');

            return false;
        }
    }

    /**
     * Přepene databázi
     *
     * @param  type    $DBName
     * @return boolean
     */
    public function selectDB($DBName = null)
    {
        if (is_null($DBName)) {
            $DBName = $this->Database;
        }
        if ($DBName) {
            if (mssql_select_db($DBName, $this->SQLLink)) {
                return true;
            } else {
                $this->LastMessage = mssql_get_last_message();
                $this->addStatusMessage($this->LastMessage, 'warning');

                return false;
            }
        }

        return null;
    }

    /**
     * Vyplní pole informací o připojení
     */
    public function makeReport()
    {
        parent::makeReport();
        $this->Report['mode'] = $this->Mode;
    }

    /**
     * Vykona MSSQL prikaz
     *
     * @param string  $QueryRaw     sql příkaz
     * @param boolean $IgnoreErrors ignorovat chyby ?
     *
     * @return SqlHandle
     */
    public function exeQuery($QueryRaw, $IgnoreErrors = false)
    {

        if (!$this->Connected) {
            if ($this->instanceCounter > 1) {
                return null;
            }
            $this->connect();
            die('buga!');
        }

        $SQLAction = trim(strtolower(current(explode(' ', $QueryRaw))));

        $this->Result = null;
        $this->LastMessage = null;
        $this->LastInsertID = null;

        $QueryRaw = $this->sanitizeQuery($QueryRaw);

        if ($SQLAction == 'insert') {
            $QueryRaw.=" SELECT @@IDENTITY as InsertId";
        }

        $this->LastQuery = $QueryRaw;

        if ($this->Mode == 'online') {
            //ob_start();
            $this->Result = mssql_query($QueryRaw, $this->SQLLink);
            $this->LastMessage = mssql_get_last_message();
            if (($this->LastMessage == 'The statement has been terminated.') || !$this->Result) {
                $this->ErrorText = $this->LastMessage . ":\n" . $this->LastQuery;

                if (EaseShared::isCli()) {
                    if (function_exists('xdebug_call_function'))
                        echo "\nVolano tridou <b>" . xdebug_call_class() . ' v souboru ' . xdebug_call_file() . ":" . xdebug_call_line() . " funkcí " . xdebug_call_function() . "\n";
                    echo "\n$QueryRaw\n\n#" . $this->ErrorNumber . ":" . $this->ErrorText;
                } else {
                    echo "<br clear=all><pre class=\"error\" style=\"border: red 1px dahed; \">";
                    if (function_exists('xdebug_print_function_stack')) {
                        xdebug_print_function_stack("Volano tridou " . xdebug_call_class() . ' v souboru ' . xdebug_call_file() . ":" . xdebug_call_line() . " funkci " . xdebug_call_function() . '');
                    }
                    echo "<br clear=all>$QueryRaw\n\n<br clear=\"all\">#" . $this->ErrorNumber . ":<strong>" . $this->ErrorText . '</strong></pre></br>';
                }
                $this->logError();
                $this->error('ExeQuery: #' . $this->ErrorNumber . ': ' . $this->ErrorText . "\n" . $QueryRaw);

                //ob_end_clean();
                return false;
            }
            //ob_end_clean();

            switch ($SQLAction) {
                case 'select':
                case 'show':
                    $this->NumRows = @mssql_num_rows($this->Result);
                    break;
                case 'insert':
                    $this->LastInsertID = (int) current(mssql_fetch_row($this->Result));

                    if (!$this->LastInsertID) {
                        /*
                          $lidquery_raw  = 'SELECT SCOPE_IDENTITY() AS LastInsertID';
                          if ($lidresult = mssql_query($lidquery_raw,$this->SQLLink))
                          $this->LastInsertID = current(mssql_fetch_row($lidresult));
                          if (!$this->LastInsertID) */
                        $this->error('Vkládání nevrátilo InsertID :' . $this->utf8($this->LastMessage . ":\n" . $this->LastQuery));
                    }

                case 'update':
                case 'replace':
                case 'delete':
                    $this->NumRows = mssql_rows_affected($this->SQLLink);
                    break;
                default:
                    $this->NumRows = null;
            }
        } else { //Offline MOD
            if ($this->debug) {
                $this->addToLog('Offline Query:' . $this->Utf8($this->LastQuery), 'warning');
            }
        }

        return $this->Result;
    }

    /**
     * Vrátí výsledek SQL dotazu jako pole
     *
     * @param string          $QueryRaw         SQL příkaz
     * @param string||boolean $KeyColumnToIndex sloupeček pro indexaci
     *
     * @return array
     */
    public function queryToArray($QueryRaw, $KeyColumnToIndex = false)
    {
        $this->ResultArray = null;
        $this->Result = $this->exeQuery($QueryRaw);
        if (!$this->Result) {
            return null;
        }
        if (is_string($KeyColumnToIndex)) {
            while ($DataRow = mssql_fetch_assoc($this->Result)) {
                $this->ResultArray[$DataRow[$KeyColumnToIndex]] = $DataRow;
            }
        } else {
            if (($KeyColumnToIndex == true) && isset($this->MSKeyColumn)) {
                while ($DataRow = mssql_fetch_assoc($this->Result)) {
                    $this->ResultArray[$DataRow[$this->MSKeyColumn]] = $DataRow;
                }
            } else {
                while ($DataRow = mssql_fetch_assoc($this->Result)) {
                    $this->ResultArray[] = $DataRow;
                }
            }
        }
        if (count($this->ResultArray)) {
            if ($this->WinToUtfRecode) {
                $this->ResultArray = $this->recursiveIconv('windows-1250', 'utf-8', $this->ResultArray);
            }

            return $this->ResultArray;
        } else {
            return null;
        }
    }

    /**
     * Vrací počet položek v tabulce
     *
     * @param string $TableName
     *
     * @return int unsigned
     */
    public function getTableNumRows($TableName = null)
    {
        if (!$TableName) {
            $TableName = $this->TableName;
        }
        $TableRowsCount = @$this->queryToArray('SELECT count(*) AS NumRows FROM [' . $this->easeAddSlashes($TableName) . ']');

        return $TableRowsCount[0]['NumRows'];
    }

    /**
     * Vrátí strukturu SQL tabulky
     *
     * @param string $TableName jméno tabulky
     *
     * @return array
     */
    public function describe($TableName = null)
    {
        if (!parent::describe($TableName)) {
            return null;
        }

        $QueryRaw = "SELECT COLUMN_NAME,DATA_TYPE,CHARACTER_MAXIMUM_LENGTH,IS_NULLABLE,NUMERIC_PRECISION,DOMAIN_NAME FROM INFORMATION_SCHEMA.Columns WHERE TABLE_NAME = '$TableName'";
        $StructQuery = $this->exeQuery($QueryRaw);
        $TableStruct = mssql_num_rows($StructQuery);
        if ($TableStruct) {
            while ($col = mssql_fetch_assoc($StructQuery)) {
                if ($col['COLUMN_NAME'] == $this->KeyColumn) {
                    $TableStructure[$col['COLUMN_NAME']]['key'] = 'primary';
                }
                if ($col['DOMAIN_NAME'] == 'COUNTER') {
                    $TableStructure[$col['COLUMN_NAME']]['key'] = 'primary';
                }
                if (($col['CHARACTER_MAXIMUM_LENGTH']) && ($col['DATA_TYPE'] != 'text')) {
                    $TableStructure[$col['COLUMN_NAME']]['size'] = $col['CHARACTER_MAXIMUM_LENGTH'];
                } else {
                    if (isset($col['NUMERIC_PRECISION'])) {
                        $TableStructure[$col['COLUMN_NAME']]['size'] = $col['NUMERIC_PRECISION'];
                    }
                }
                $TableStructure[$col['COLUMN_NAME']]['type'] = $col['DATA_TYPE'];
            }
        }

        return $TableStructure;
    }

    /**
     * Prepare columns to query fragment
     *
     * @param array   $data            asociativní pole
     * @param boolean $PermitKeyColumn nepřeskočit klíčový sloupeček ?
     *
     * @return string
     */
    public function prepCols($data, $PermitKeyColumn = false)
    {
        $Values = '';
        $Columns = '';
        $ANSIDate = null;
        foreach ($data as $Column => $value) {
            if (!$PermitKeyColumn) {
                if ($Column == $this->KeyColumn) {
                    continue;
                }
            }
            //				$deklarace[]='DECLARE @'.$col.' './*funkce_pro_mssql_typ($tabulka.$col)*/.';';

            switch (gettype($value)) {
                case "boolean":
                    if ($value)
                        $Values.=" 'True',";
                    else
                        $Values.=" 'False',";
                    break;
                case "null":
                    $Values.=" null,";
                    break;
                case "integer":
                case "double":
                    $Values.=' ' . str_replace(',', '.', $value) . ',';
                    break;
                default:
//                    $ANSIDate = $this->LocaleDateToANSIDate($value);
                    $ANSIDate = $value;
                    if ($ANSIDate) {
                        $value = $ANSIDate;
                    }
                    if (strtolower($value) == 'getdate()') {
                        $Values.=" GetDate(),";
                    } else {
                        $Values.=" '" . addslashes($value) . "',";
                    }
            }
            $Columns.=" [$Column],";
        }
        $Columns = substr($Columns, 0, -1);
        $Values = substr($Values, 0, -1);

        return array($Columns, $Values);
    }

    /**
     * Give Update query fragment
     *
     * @param array  $data         asociativní pole dat
     * @param array  $CheckColumns kontrolovat délky řetězců na překročení místa
     * @param string $TableName    jméno tabulky
     *
     * @return string
     */
    public function prepUpdate($data, $CheckColumns = false, $TableName = null)
    {
        if (!count($data)) {
            $this->error('Missing data for PrepUpdate');

            return null;
        }
        if (!$TableName) {
            $TableName = $this->TableName;
        }
        if ($CheckColumns || count($this->TableStructure)) {
            $this->fixColumnsLength($data);
        }

        $updates = '';
        foreach ($data as $Column => $value) {
            if ($Column == $this->KeyColumn)
                continue;
            switch (gettype($value)) {
                case 'boolean':
                    if ($value) {
                        $value = ' 1 ';
                    } else {
                        $value = ' 0 ';
                    }
                    break;
                case 'null':
                case 'null':
                    $value = ' null ';
                    break;

                case 'integer':
                case 'double':
                    $value = ' ' . str_replace(',', '.', $value);
                    break;
                case 'string':
                default:
//                    $ANSIDate = $this->LocaleDateToANSIDate($value);
//                    if ($ANSIDate)
//                        $value = $ANSIDate;

                    if (strtolower($value) != 'getdate()')
                        $value = " '$value' ";
                    break;
            }
            $updates.=" [$Column] = $value,";
        }

        return substr($updates, 0, -1);
    }

    /**
     * Vrací framgment SQL dotazu pro SELECT
     *
     * @param array $data pole ze kterého se vytvoří fragment SQL dotazu
     *                    array('date'=>'','name'=>'','id AS recordID'=>10)
     *
     * @return string vzorový vstup vrátí: "`date`,`name`,`id` AS recordID"
     */
    public function prepSelect($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        $Updates = '';
        foreach ($data as $Column => $value) {
            if (($Column == $this->KeyColumn) && (count($data) != 1))
                continue;
            if ($value[0] == '!') {
                $operator = ' != ';
                $value = substr($value, 1);
            } else {
                $operator = ' = ';
            }
            //				$deklarace[]='DECLARE @'.$col.' './*funkce_pro_mssql_typ($tabulka.$col)*/.';';

            if (is_bool($value)) {
                if ($value === null) {
                    $value = " null,";
                } elseif ($value) {
                    $value = " 1";
                } else {
                    $value = " 0";
                }   // 	if (is_null($val)) {
            } elseif (!is_string($value))
                if (is_float($value))
                    $value = ' ' . str_replace(',', '.', $value);
                else
                    $value = " $value";
            else {
                $value = " '$value'";
                $operator = ' LIKE ';
            }

            // 				echo "<pre>$col:\n"; var_dump($val); echo '</pre>';

            $Updates.=" [$Column] $operator $value AND";
        }

        return substr($Updates, 0, -3);
    }

    /**
     * Table presence test
     *
     * @param string $TableName
     *
     * @return boolen
     */
    public function tableExist($TableName = null)
    {
        if (!parent::TableExist($TableName))
            return null;
        $this->exeQuery("SELECT name FROM sysobjects WHERE name = '" . $TableName . "' AND OBJECTPROPERTY(id, 'IsUserTable') = 1");
        if ($this->NumRows)
            return true;
        else
            return false;
    }

    /**
     * Vytvoří tabulku podle struktůry
     *
     * @param array  $TableStructure struktura tabulky
     * @param string $TableName      jméno tabulky
     *
     * @return boolean Success
     */
    public function createTable(&$TableStructure = null, $TableName = null)
    {

        /*
          CREATE TABLE [dbo].[synctest2](
          [ID] [int] IDENTITY(1,1) NOT null,
          [IDS] [nchar](10) null,
          [VALUE] [nchar](10) null,
          [CREATED] [datetime] null,
          [MODIFIED] [smalldatetime] null,
          [VPrID] [int] null,
          CONSTRAINT [PK_synctest2] PRIMARY KEY CLUSTERED
          (
          [ID] ASC
          )WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
          ) ON [PRIMARY]
         */
    }

    /**
     * Odstraní z SQL dotazu "nebezpecne" znaky
     *
     * @param string $QueryRaw SQL Query
     *
     * @return string SQL Query
     */
    public function sanitizeQuery($QueryRaw)
    {
        $SanitizedQuery = str_replace(array("\'", '\"'), array("''", '""'), parent::SanitizeQuery($QueryRaw));

        return $SanitizedQuery;
    }

    /**
     * Vrací seznam tabulek v aktuálné použité databázi
     *
     * @param boolean $Sort vysledek ještě setřídit
     *
     * @return array
     */
    public function listTables($Sort = false)
    {
        $TablesList = array();
        $TablesQuery = $this->queryToArray("SELECT TABLE_SCHEMA,TABLE_NAME, OBJECTPROPERTY(object_id(TABLE_NAME), N'IsUserTable') AS type FROM INFORMATION_SCHEMA.TABLES");
        if (is_array($TablesQuery)) {
            foreach ($TablesQuery as $TableName) {
                $TablesList[$TableName['TABLE_NAME']] = $TableName['TABLE_NAME'];
            }
            if ($Sort) {
                asort($TablesList, SORT_LOCALE_STRING);
            }

            return $TablesList;
        }

        return null;
    }

    /**
     * Close SQL connecton
     *
     * @return boolean
     */
    public function close()
    {
        if ($this->SQLLink) {
            return mssql_close($this->SQLLink);
        } else {
            return null;
        }
    }

}

/**
 * Testuje dostupnost MSSQL serveru
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseMSDbPinger extends EaseDbMSSQL
{

    /**
     * Teste provedeme již při připojení
     */
    public function connect()
    {
        if (!$this->ping()) {
            $this->writeLockFile();
        } else {
            $this->dropLockFile();
        }
    }

    /**
     * Vytvoří zamykací soubor
     *
     * @param string $LockFile použij jiný název zamykacího souboru
     *
     * @return boolean
     */
    public function writeLockFile($LockFile = null)
    {
        if (!$LockFile) {
            $LockFile = $this->Lockfile;
        }
        if (file_exists($LockFile)) {
            $this->addToLog('WriteLockFile: Allready exists');

            return;
        }
        $LockFileHandle = fopen($LockFile, 'w+');
        if ($LockFileHandle) {
            $this->addToLog('WriteLockFile: LockFile written');
            fclose($LockFileHandle);
            $this->Mode = 'offline';

            return true;
        } else {
            $this->error('WriteLockFile: Lockfile ' . realpath($this->Lockfile) . ' could not be written', $this->TestDirectory(dirname($this->Lockfile)));

            return false;
        }
    }

    /**
     * Odstraní zamykací soubor
     *
     * @param string $LockFile cesta k zamykacímu souboru
     */
    public function dropLockFile($LockFile = null)
    {
        if (!$LockFile) {
            $LockFile = $this->Lockfile;
        }
        if (!file_exists($LockFile)) {
            $this->addToLog('MSSQL Lockfile ' . $LockFile . ' doesn\'t exist', 'warning');

            return true;
        }

        unlink($LockFile);
        if (file_exists($LockFile)) {
            $this->error('DropLockFile: Lockfile ' . realpath($this->Lockfile) . ' alive', $this->TestDirectory(dirname($this->Lockfile)));

            return false;
        }
        $this->addToLog('DropLockFile: ' . realpath($this->Lockfile), 'succes');

        return true;
    }

    /**
     * Pokusí se po připojení socketem k SQLserveru
     *
     * @param boolean $Succes vynucení výsledku
     *
     * @return boolan
     */
    public function ping($Succes = null)
    {
        $Socket = @fsockopen($this->Server, 1433, $errno, $LastAction);
        if (!$Socket) {
            return parent::Ping(false);
        } else {
            fclose($Socket);
        }

        return parent::Ping(true);
    }

}
