<?php

namespace Ease\SQL;

/**
 * Podpora MSSQL databáze.
 *
 * @deprecated since version 2.0
 *
 * @category   Sql
 *
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2009-2011 Vitex@hippy.cz (G)
 */

/**
 * Basic Database Layer.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class MSSQL extends SQL
{
    public $Debug        = false;
    public $NumRows      = 0;
    public $lastInsertID = 0;
    public $LastQuery    = '';
    public $Result       = null;
    public $ResultArray  = null;
    public $dbLink       = null;
    public $Lockfile     = 'mssql-offline';

    /**
     * Hack pro svéhlavé FreeTDS na windows co ignoruje konfiguraci.
     *
     * @var bool
     */
    public $WinToUtfRecode = false;

    /**
     * Kolikrát se pokusit připojit před offline.
     *
     * @var type
     */
    public $ConnectAttempts = 10;

    /**
     * Indikator skutečného připojení k MSSQL.
     *
     * @var bool
     */
    public $Connected = false;

    /**
     * Nastavení vlastností přípojení.
     *
     * @var array
     */
    public $ConnectionSettings = [
        'ANSI_NULLS' => 'ON',
        'QUOTED_IDENTIFIER' => 'ON',
        'CONCAT_NULL_YIELDS_NULL' => 'ON',
        'ANSI_WARNINGS' => 'ON',
        'ANSI_PADDING' => 'ON',];

    /**
     * Saves obejct instace (singleton...).
     */
    private static $_instance = null;

    /**
     * Kolikáty pokus o připojení ?
     *
     * @var int
     */
    public $instanceCounter = 0;

    /**
     * MSSQL mode.
     *
     * @var string online|offline
     */
    public $Mode = 'online'; // 'online' | 'offline'

    /**
     * Database layer.
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
        if (!isset($this->username) && defined('MS_DB_SERVER_USERNAME')) {
            $this->username = constant('MS_DB_SERVER_USERNAME');
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
            $ClassName       = __CLASS__;
            self::$_instance = new $ClassName($Mode);
        }

        return self::$_instance;
    }

    /**
     * Připojí se k MSSQL.
     *
     * @return bool
     */
    public function connect()
    {
        if (is_null($this->SQLLink)) {
            if (++$this->instanceCounter > 1) {
                return;
            }
        }
        if (!function_exists('mssql_connect')) {
            $this->error('ConnectReal: MSSQL is not compiled in');
            $this->Status      = 'error';
            $this->LastMessage = 'MSSQL is not compiled in';
            $this->makeReport();

            return;
        }
        $this->SQLLink = mssql_connect($this->Server, $this->username,
            $this->Password);
        if ($this->SQLLink) {
            $this->Connected = true;
            mssql_min_error_severity(2);
            $this->selectDB($this->Database);
            parent::connect();

            return $this->Status;
        } else {
            $this->LastMessage = mssql_get_last_message();
            $this->addStatusMessage('connect: '.$this->LastMessage, 'warning');

            return false;
        }
    }

    /**
     * Přepene databázi.
     *
     * @param type $DBName
     *
     * @return boolean|null
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

        return;
    }

    /**
     * Vyplní pole informací o připojení.
     */
    public function makeReport()
    {
        parent::makeReport();
        $this->Report['mode'] = $this->Mode;
    }

    /**
     * Vykona MSSQL prikaz.
     *
     * @param string $QueryRaw     sql příkaz
     * @param bool   $IgnoreErrors ignorovat chyby ?
     *
     * @return SqlHandle
     */
    public function exeQuery($QueryRaw, $IgnoreErrors = false)
    {
        if (!$this->Connected) {
            if ($this->instanceCounter > 1) {
                return;
            }
            $this->connect();
            die('buga!');
        }

        $SQLAction = trim(strtolower(current(explode(' ', $QueryRaw))));

        $this->Result       = null;
        $this->LastMessage  = null;
        $this->lastInsertID = null;

        $QueryRaw = $this->sanitizeQuery($QueryRaw);

        if ($SQLAction == 'insert') {
            $QueryRaw .= ' SELECT @@IDENTITY as InsertId';
        }

        $this->LastQuery = $QueryRaw;

        if ($this->Mode == 'online') {
            //ob_start();
            $this->Result      = mssql_query($QueryRaw, $this->SQLLink);
            $this->LastMessage = mssql_get_last_message();
            if (($this->LastMessage == 'The statement has been terminated.') || !$this->Result) {
                $this->errorText = $this->LastMessage.":\n".$this->LastQuery;

                if (EaseShared::isCli()) {
                    if (function_exists('xdebug_call_function')) {
                        echo "\nVolano tridou <b>".xdebug_call_class().' v souboru '.xdebug_call_file().':'.xdebug_call_line().' funkcí '.xdebug_call_function()."\n";
                    }
                    echo "\n$QueryRaw\n\n#".$this->errorNumber.':'.$this->errorText;
                } else {
                    echo '<br clear=all><pre class="error" style="border: red 1px dahed; ">';
                    if (function_exists('xdebug_print_function_stack')) {
                        xdebug_print_function_stack('Volano tridou '.xdebug_call_class().' v souboru '.xdebug_call_file().':'.xdebug_call_line().' funkci '.xdebug_call_function().'');
                    }
                    echo "<br clear=all>$QueryRaw\n\n<br clear=\"all\">#".$this->errorNumber.':<strong>'.$this->errorText.'</strong></pre></br>';
                }
                $this->logError();
                $this->error('ExeQuery: #'.$this->errorNumber.': '.$this->errorText."\n".$QueryRaw);

                //ob_end_clean();
                return false;
            }
            //ob_end_clean();

            switch ($SQLAction) {
                case 'select':
                case 'show':
                    $this->NumRows      = @mssql_num_rows($this->Result);
                    break;
                case 'insert':
                    $this->lastInsertID = (int) current(mssql_fetch_row($this->Result));

                    if (!$this->lastInsertID) {
                        /*
                          $lidquery_raw  = 'SELECT SCOPE_IDENTITY() AS lastInsertID';
                          if ($lidresult = mssql_query($lidquery_raw,$this->SQLLink))
                          $this->lastInsertID = current(mssql_fetch_row($lidresult));
                          if (!$this->lastInsertID) */
                        $this->error('Vkládání nevrátilo InsertID :'.$this->utf8($this->LastMessage.":\n".$this->LastQuery));
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
                $this->addToLog('Offline Query:'.$this->Utf8($this->LastQuery),
                    'warning');
            }
        }

        return $this->Result;
    }

    /**
     * Vrátí výsledek SQL dotazu jako pole.
     *
     * @param string       $queryRaw         SQL příkaz
     * @param string||bool $KeyColumnToIndex sloupeček pro indexaci
     *
     * @return array
     */
    public function queryToArray($queryRaw, $KeyColumnToIndex = false)
    {
        $this->resultArray = null;
        $this->Result      = $this->exeQuery($queryRaw);
        if (!$this->Result) {
            return;
        }
        if (is_string($KeyColumnToIndex)) {
            while ($DataRow = mssql_fetch_assoc($this->Result)) {
                $this->resultArray[$DataRow[$KeyColumnToIndex]] = $DataRow;
            }
        } else {
            if (($KeyColumnToIndex == true) && isset($this->MSKeyColumn)) {
                while ($DataRow = mssql_fetch_assoc($this->Result)) {
                    $this->resultArray[$DataRow[$this->MSKeyColumn]] = $DataRow;
                }
            } else {
                while ($DataRow = mssql_fetch_assoc($this->Result)) {
                    $this->resultArray[] = $DataRow;
                }
            }
        }
        if (count($this->resultArray)) {
            if ($this->WinToUtfRecode) {
                $this->resultArray = $this->recursiveIconv('windows-1250',
                    'utf-8', $this->resultArray);
            }

            return $this->resultArray;
        } else {
            return;
        }
    }

    /**
     * Vrací počet položek v tabulce.
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
        $TableRowsCount = $this->queryToArray('SELECT count(*) AS NumRows FROM ['.$this->easeAddSlashes($TableName).']');

        return $TableRowsCount[0]['NumRows'];
    }

 

    /**
     * Prepare columns to query fragment.
     *
     * @param array $data            asociativní pole
     * @param bool  $PermitKeyColumn nepřeskočit klíčový sloupeček ?
     *
     * @return string[]
     */
    public function prepCols($data, $PermitKeyColumn = false)
    {
        $values   = '';
        $columns  = '';
        $ANSIDate = null;
        foreach ($data as $Column => $value) {
            if (!$PermitKeyColumn) {
                if ($Column == $this->KeyColumn) {
                    continue;
                }
            }
            //				$deklarace[]='DECLARE @'.$col.' './*funkce_pro_mssql_typ($tabulka.$col)*/.';';

            switch (gettype($value)) {
                case 'boolean':
                    if ($value) {
                        $values .= " 'True',";
                    } else {
                        $values .= " 'False',";
                    }
                    break;
                case 'null':
                    $values .= ' null,';
                    break;
                case 'integer':
                case 'double':
                    $values .= ' '.str_replace(',', '.', $value).',';
                    break;
                default:
                    //                    $ANSIDate = $this->LocaleDateToANSIDate($value);
                    $ANSIDate = $value;
                    if ($ANSIDate) {
                        $value = $ANSIDate;
                    }
                    if (strtolower($value) == 'getdate()') {
                        $values .= ' GetDate(),';
                    } else {
                        $values .= " '".addslashes($value)."',";
                    }
            }
            $columns .= " [$Column],";
        }
        $columns = substr($columns, 0, -1);
        $values  = substr($values, 0, -1);

        return [$columns, $values];
    }

    /**
     * Give Update query fragment.
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

            return;
        }
        if (!$TableName) {
            $TableName = $this->TableName;
        }
        if ($CheckColumns || count($this->TableStructure)) {
            $this->fixColumnsLength($data);
        }

        $updates = '';
        foreach ($data as $Column => $value) {
            if ($Column == $this->KeyColumn) {
                continue;
            }
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
                    $value = ' '.str_replace(',', '.', $value);
                    break;
                case 'string':
                default:
                    //                    $ANSIDate = $this->LocaleDateToANSIDate($value);
                    //                    if ($ANSIDate)
                    //                        $value = $ANSIDate;

                    if (strtolower($value) != 'getdate()') {
                        $value = " '$value' ";
                    }
                    break;
            }
            $updates .= " [$Column] = $value,";
        }

        return substr($updates, 0, -1);
    }

    /**
     * Vrací framgment SQL dotazu pro SELECT.
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
        $updates = '';
        foreach ($data as $column => $value) {
            if (($column == $this->KeyColumn) && (count($data) != 1)) {
                continue;
            }
            if ($value[0] == '!') {
                $operator = ' != ';
                $value    = substr($value, 1);
            } else {
                $operator = ' = ';
            }
            //				$deklarace[]='DECLARE @'.$col.' './*funkce_pro_mssql_typ($tabulka.$col)*/.';';

            if (is_bool($value)) {
                if ($value === null) {
                    $value = ' null,';
                } elseif ($value) {
                    $value = ' 1';
                } else {
                    $value = ' 0';
                }   // 	if (is_null($val)) {
            } elseif (!is_string($value)) {
                if (is_float($value)) {
                    $value = ' '.str_replace(',', '.', $value);
                } else {
                    $value = " $value";
                }
            } else {
                $value    = " '$value'";
                $operator = ' LIKE ';
            }

            // 				echo "<pre>$col:\n"; var_dump($val); echo '</pre>';

            $updates .= " [$column] $operator $value AND";
        }

        return substr($updates, 0, -3);
    }

    /**
     * Table presence test.
     *
     * @param string $TableName
     *
     * @return null|boolean
     */
    public function tableExist($TableName = null)
    {
        if (!parent::TableExist($TableName)) {
            return;
        }
        $this->exeQuery("SELECT name FROM sysobjects WHERE name = '".$TableName."' AND OBJECTPROPERTY(id, 'IsUserTable') = 1");
        if ($this->NumRows) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Odstraní z SQL dotazu "nebezpecne" znaky.
     *
     * @param string $queryRaw SQL Query
     *
     * @return string SQL Query
     */
    public function sanitizeQuery($queryRaw)
    {
        $SanitizedQuery = str_replace(["\'", '\"'], ["''", '""'],
            parent::SanitizeQuery($queryRaw));

        return $SanitizedQuery;
    }

    /**
     * Vrací seznam tabulek v aktuálné použité databázi.
     *
     * @param bool $sort vysledek ještě setřídit
     *
     * @return array
     */
    public function listTables($sort = false)
    {
        $TablesList  = [];
        $TablesQuery = $this->queryToArray("SELECT TABLE_SCHEMA,TABLE_NAME, OBJECTPROPERTY(object_id(TABLE_NAME), N'IsUserTable') AS type FROM INFORMATION_SCHEMA.TABLES");
        if (is_array($TablesQuery)) {
            foreach ($TablesQuery as $TableName) {
                $TablesList[$TableName['TABLE_NAME']] = $TableName['TABLE_NAME'];
            }
            if ($sort) {
                asort($TablesList, SORT_LOCALE_STRING);
            }

            return $TablesList;
        }

        return;
    }

    /**
     * Close SQL connecton.
     *
     * @return boolean|null
     */
    public function close()
    {
        if ($this->SQLLink) {
            return mssql_close($this->SQLLink);
        } else {
            return;
        }
    }
}