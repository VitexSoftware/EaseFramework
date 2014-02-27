<?php

/**
 * Obsluha MySQL
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2012 Vitex@hippy.cz (G)
 */
require_once 'Ease/EaseSQL.php';

/**
 * Třída pro práci s MySQL
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EaseDbMySqli extends EaseSQL
{

    /**
     * MySQLi class instance
     * @var mysqli
     */
    public $SQLLink = null; // MS SQL link identifier
    /**
     * SQLLink result
     * @var mysqli_result
     */
    public $result = null;
    public $status = false; //Pripojeno ?
    public $LastQuery = '';
    public $NumRows = 0;
    public $Debug = false;
    public $keyColumn = '';
    public $data = null;
    public $Charset = 'utf8';
    public $Collate = 'utf8_czech_ci';

    /**
     * Povolit Explain každého dotazu do logu ?
     * @var bool
     */
    public $ExplainMode = false;

    /**
     * Nastavení vlastností přípojení
     * @var array
     */
    public $ConnectionSettings = array(
        'NAMES' => 'utf8'
    );

    /**
     * Saves obejct instace (singleton...)
     */
    private static $instance = null;

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako konstruktor)
     * se bude v ramci behu programu pouzivat pouze jedna jeho instance (ta prvni).
     *
     * @link http://docs.php.net/en/language.oop5.patterns.html Dokumentace a priklad
     */
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            $Class = __CLASS__;
            self::$instance = new $Class();
        }

        return self::$instance;
    }

    /**
     * Escapes special characters in a string for use in an SQL statement
     *
     * @param string $Text
     *
     * @return string
     */
    public function addSlashes($Text)
    {
        return $this->SQLLink->real_escape_string($Text);
    }

    /**
     * Připojí se k mysql databázi
     */
    public function connect()
    {
        $this->SQLLink = new mysqli($this->Server, $this->Username, $this->Password);
        if ($this->SQLLink->connect_errno) {
            $this->addStatusMessage('Connect: error #' . $this->SQLLink->connect_errno . ' ' . $this->SQLLink->connect_error, 'error');

            return FALSE;
        } else {
            if ($this->selectDB($this->Database)) {
                $this->errorText = $this->SQLLink->error;
                parent::connect();
            } else {
                return FALSE;
            }
        }
    }

    /**
     * Změní aktuálně použitou databázi
     *
     * @param string $DbName
     *
     * @return boolean
     */
    public function selectDB($DbName = null)
    {
        parent::selectDB($DbName);
        $Change = $this->SQLLink->select_db($DbName);
        if ($Change) {
            $this->Database = $DbName;
        } else {
            $this->errorText = $this->SQLLink->error;
            $this->errorNumber = $this->SQLLink->errno;
            $this->addStatusMessage('Connect: error #' . $this->errorNumber . ' ' . $this->errorText, 'error');
            $this->logError();
        }

        return $Change;
    }

    /**
     * Vykoná QueryRaw a vrátí výsledek
     *
     * @param string  $queryRaw
     * @param boolean $ignoreErrors
     *
     * @return SQLhandle
     */
    public function exeQuery($queryRaw, $ignoreErrors = false)
    {
        $queryRaw = $this->sanitizeQuery($queryRaw);
        $this->LastQuery = $queryRaw;
        $this->LastInsertID = null;
        $this->errorText = null;
        $this->errorNumber = null;
        $sqlAction = trim(strtolower(current(explode(' ', $queryRaw))));
        do {
            $this->result = $this->SQLLink->query($queryRaw);
            $this->errorNumber = $this->SQLLink->errno;
            $this->errorText = $this->SQLLink->error;
            if (!$this->result && !$ignoreErrors) {
                if (EaseShared::isCli()) {
                    if (function_exists('xdebug_call_function'))
                        echo "\nVolano tridou <b>" . xdebug_call_class() . ' v souboru ' . xdebug_call_file() . ":" . xdebug_call_line() . " funkcí " . xdebug_call_function() . "\n";
                    echo "\n$queryRaw\n\n#" . $this->errorNumber . ":" . $this->errorText;
                } else {
                    echo "<br clear=all><pre class=\"error\" style=\"border: red 1px dahed; \">";
                    if (function_exists('xdebug_print_function_stack')) {
                        xdebug_print_function_stack("Volano tridou <b>" . xdebug_call_class() . '</b> v souboru <b>' . xdebug_call_file() . ":" . xdebug_call_line() . "</b> funkci <b>" . xdebug_call_function() . '</b>');
                    }
                    echo "<br clear=all>$queryRaw\n\n<br clear=\"all\">#" . $this->errorNumber . ":<strong>" . $this->errorText . '</strong></pre></br>';
                }
                $this->logError();
                $this->error('ExeQuery: #' . $this->errorNumber . ': ' . $this->errorText . "\n" . $queryRaw);
                if ($this->errorNumber == 2006) {
                    $this->reconnect();
                } else {
                    return null;
                }
            }
        } while ($this->errorNumber == 2006); // 'MySQL server has gone away'

        switch ($sqlAction) {
            case 'select':
            case 'show':
                if (!$this->errorText) {
                    $this->NumRows = $this->result->num_rows;
                }
                break;
            case 'insert':
                if (!$this->errorText) {
                    $this->LastInsertID = $this->SQLLink->insert_id;
                }
            case 'update':
            case 'replace':
            case 'delete':
            case 'alter':
                $this->NumRows = $this->SQLLink->affected_rows;
                break;
            default:
                $this->NumRows = null;
        }
        if ($this->ExplainMode) {
            $EexplainQuery = $this->SQLLink->query('EXPLAIN ' . $queryRaw);
            if ($EexplainQuery) {
                $ExplainedQuery = $EexplainQuery->fetch_assoc();
                $this->addToLog('Explain: ' . $queryRaw . "\n" . $this->printPreBasic($ExplainedQuery), 'explain');
            }
        }

        return $this->result;
    }

    /**
     * vraci vysledek SQL dotazu $QueryRaw jako pole (uchovavane take jako $this->Resultarray)
     *
     * @param string $queryRaw
     * @param string $keyColumnToIndex umožní vrátit pole výsledků číslovaných podle $DataRow[$KeyColumnToIndex];
     *
     * @return array
     */
    public function queryToArray($queryRaw, $keyColumnToIndex = false)
    {
        $resultArray = array();
        if ($this->exeQuery($queryRaw)) {
            if (is_string($keyColumnToIndex)) {
                while ($dataRow = $this->result->fetch_assoc()) {
                    $resultArray[$dataRow[$keyColumnToIndex]] = $dataRow;
                }
            } else {
                if (($keyColumnToIndex == true) && isset($this->myKeyColumn)) {
                    while ($dataRow = $this->result->fetch_assoc()) {
                        $resultArray[$dataRow[$this->myKeyColumn]] = $dataRow;
                    }
                } else {
                    while ($dataRow = $this->result->fetch_assoc()) {
                        $resultArray[] = $dataRow;
                    }
                }
            }
        } else {
            return null;
        }

        return $resultArray;
    }

    /**
     * vloží obsah pole $data do předvolené tabulky $this->myTable
     *
     * @param string $data
     *
     * @return sqlresult
     */
    public function arrayToInsert($data)
    {
        return $this->exeQuery('INSERT INTO `' . $this->TableName . '` SET ' . $this->arrayToQuery($data));
    }

    /**
     * upravi obsah zaznamu v predvolene tabulce $this->myTable, kde klicovy sloupec
     * $this->myKeyColumn je hodnota v klicovem sloupci hodnotami z pole $data
     *
     * @param array $data  asociativní pole dat
     * @param int   $KeyID id záznamu. Není li uveden použije se aktuální
     *
     * @return sqlresult
     *
     */
    public function arrayToUpdate($data, $KeyID = null)
    {
        if (!$KeyID) {
            $IDCol = $data[$this->keyColumn];
        }
        unset($data[$this->keyColumn]);

        return $this->exeQuery('UPDATE ' . $this->TableName . ' SET ' . $this->arrayToQuery($data) . ' WHERE ' . $this->keyColumn . '=' . $IDCol);
    }

    /**
     * z pole $data vytvori fragment SQL dotazu za WHERE (klicovy sloupec
     * $this->myKeyColumn je preskocen pokud neni $key false)
     *
     * @param array   $data
     * @param boolean $Key
     *
     * @return string
     */
    public function arrayToQuery($data, $Key = true)
    {
        $updates = '';
        foreach ($data as $Column => $value) {
            if (!strlen($Column)) {
                continue;
            }
            if (($Column == $this->keyColumn) && $Key) {
                continue;
            }
            switch (gettype($value)) {
                case 'integer':
                    $value = " $value ";
                    break;
                case 'float':
                case 'double':
                    $value = ' ' . str_replace(',', '.', $value) . ' ';
                    break;
                case 'boolean':
                    if ($value) {
                        $value = ' 1 ';
                    } else {
                        $value = ' 0 ';
                    }
                    break;
                case 'NULL':
                    $value = ' null ';
                    break;
                case 'string':
                    if ($value != 'NOW()')
                        if (!strstr($value, "\'")) {
                            $value = " '" . str_replace("'", "\'", $value) . "' ";
                        } else {
                            $value = " '$value' ";
                        }
                    break;
                default:
                    $value = " '$value' ";
            }

            $updates.=" `$Column` = $value,";
        }

        return substr($updates, 0, -1);
    }

    /**
     * Generuje fragment MySQL dotazu z pole data
     *
     * @param array  $data Pokud hodnota zacina znakem ! Je tento odstranen a generovan je negovany test
     * @param string $ldiv typ generovane podminky AND/OR
     *
     * @return sql
     */
    public function prepSelect($data, $ldiv = 'AND')
    {
        $operator = null;
        $conditions = array();
        $conditionsII = array();
        foreach ($data as $column => $value) {
            if (is_integer($column)) {
                $conditionsII[] = $value;
                continue;
            }
            if (($column == $this->keyColumn) && ($this->keyColumn == '')) {
                continue;
            }
            if (is_string($value) && (($value == '!=""') || ($value == "!=''"))) {
                $conditions[] = " `$column` !='' ";
                continue;
            }

            if (is_null($value)) {
                $value = 'null';
                $operator = ' IS ';
            } else {
                if (strlen($value) && ($value[0] == '!')) {
                    $operator = ' != ';
                    $value = substr($value, 1);
                } else {
                    if (($value == '!NULL') || (strtoupper($value) == 'IS NOT NULL')) {
                        $value = 'null';
                        $operator = 'IS NOT';
                    } else {
                        $operator = ' = ';
                    }
                }
                if (is_bool($value)) {
                    if ($value === null) {
                        $value.=" null,";
                    } elseif ($value) {
                        $value = " 1";
                    } else {
                        $value = " 0";
                    }
                } elseif (!is_string($value)) {
                    $value = " $value";
                } else {
                    if (strtoupper($value) == 'NOW()') {
                        $value = " 'NOW()'";
                    } else {
                        if ($value != 'null') {
                            $value = " '" . addslashes($value) . "'";
                        }
                    }
                    if ($operator == ' != ') {
                        $operator = ' NOT LIKE ';
                    } else {
                        if(is_null($operator)){
                            $operator = ' LIKE ';
                        }
                    }
                }
            }

            $conditions[] = " `$column` $operator $value ";
        }

        return trim(implode($ldiv, $conditions) . ' ' . implode(' ', $conditionsII));
    }

    /**
     * Vrací strukturu tabulky jako pole
     *
     * @param string $tableName
     *
     * @return array Struktura tabulky
     */
    public function describe($tableName = null)
    {
        if (!parent::describe($tableName)) {
            return null;
        }
        foreach ($this->queryToArray("DESCRIBE $tableName") as $column) {
            $this->TableStructure[$tableName][$column['Field']] = $column;
        }
        return $this->TableStructure;
    }

    /**
     * Vrací 1 pokud tabulka v databázi existuje
     *
     * @param string $TableName
     *
     * @return int
     */
    public function tableExist($TableName = null)
    {
        if (!parent::tableExist($TableName)) {
            return null;
        }
        $this->exeQuery("SHOW TABLES LIKE '" . $TableName . "'");
        if ($this->NumRows) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vrací počet řádek v tabulce
     *
     * @param string $TableName
     *
     * @return int
     */
    public function getTableNumRows($TableName = null)
    {
        if (!$TableName) {
            $TableName = $this->TableName;
        }
        $TableRowsCount = $this->queryToArray('SELECT count(*) AS NumRows FROM `' . $this->easeAddSlashes($TableName) . '`');

        return $TableRowsCount[0]['NumRows'];
    }

    /**
     * Vytvoří tabulku podle struktůry
     *
     * @param array  $TableStructure struktura SQL
     * @param string $TableName      název tabulky
     */
    public function createTable(& $TableStructure = null, $TableName = null)
    {
        if (!parent::createTable($TableStructure, $TableName)) {
            return null;
        }
        if ($this->exeQuery($this->createTableQuery($TableStructure, $TableName))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vyprázdní tabulku
     *
     * @param string $TableName
     *
     * @return boolean success
     */
    public function truncateTable($TableName)
    {
        $this->exeQuery('TRUNCATE ' . $TableName);
        if (!$this->getTableNumRows($TableName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vytvoří index na tabulkou
     *
     * @param string $ColumnName
     * @param bool   $Primary    create Index as Primary Key
     * @param string $TableName  if unset $this->TableName is used
     *
     * @return sql handle
     */
    public function addTableKey($ColumnName, $Primary = false, $TableName = null)
    {
        if (!$TableName) {
            $TableName = $this->TableName;
        }
        if ($Primary) {
            return $this->exeQuery("ALTER TABLE `$TableName` ADD PRIMARY KEY ( `$ColumnName` )");
        } else {
            return $this->exeQuery("ALTER TABLE `$TableName` ADD KEY ( `$ColumnName` )");
        }
    }

    /**
     * Vytvoří tabulku podle struktůry
     *
     * @param array  $tableStructure struktura tabulky
     * @param string $tableName      název tabulky
     */
    public function createTableQuery(&$tableStructure, $tableName = null)
    {
        if (!$tableName) {
            $tableName = $this->TableName;
        }
        if (!parent::createTableQuery($tableStructure, $tableName)) {
            return null;
        }
        $queryRawItems = array();
        $Indexes = array();

        $QueryRawBegin = "CREATE TABLE IF NOT EXISTS `$tableName` (\n";
        foreach ($tableStructure as $columnName => $columnProperties) {

            switch ($columnProperties['type']) {
                case 'bit':
                    $columnProperties['type'] = 'tinyint';
                    break;
                case 'money':
                case 'decimal(10,4)(19)':
                    $columnProperties['type'] = 'decimal(10,4)';
                    break;

                default:
                    break;
            }

            $rawItem = "  `" . $columnName . "` " . $columnProperties['type'];

            if (isset($columnProperties['size'])) {
                $rawItem .= '(' . $columnProperties['size'] . ') ';
            }

            if (array_key_exists('unsigned', $columnProperties) || isset($columnProperties['unsigned'])) {
                $rawItem .= " UNSIGNED ";
            }

            if (array_key_exists('null', $columnProperties)) {
                if ($columnProperties['null'] == true) {
                    $rawItem .= " NULL ";
                } else {
                    $rawItem .= " NOT NULL ";
                }
            }
            if (array_key_exists('ai', $columnProperties)) {
                $rawItem .= " AUTO_INCREMENT ";
            }

            $queryRawItems[] = $rawItem;

            if (array_key_exists('key', $columnProperties) || isset($columnProperties['key'])) {
                if (( isset($columnProperties['key']) && ($columnProperties['key'] == 'primary')) || ( isset($columnProperties['Key']) && ($columnProperties['Key'] === 'primary') )) {
                    $Indexes[] = 'PRIMARY KEY  (`' . $columnName . '`)';
                } else {
                    $Indexes[] = 'KEY  (`' . $columnName . '`)';
                }
            }
            if (array_key_exists('ai', $columnProperties)) {
                $queryRawItems[key($queryRawItems)] .= ' AUTO_INCREMENT ';
            }
            if (array_key_exists('null', $columnProperties)) {
                if ($columnProperties['null'] == true) {
                    $queryRawItems.= ' NULL ';
                } else {
                    $queryRawItems.= ' NOT NULL ';
                }
            }
            /*
              CREATE TABLE IF NOT EXISTS `synctest` (
              `id` tinyint(4) NOT null auto_increment,
              `value` varchar(64) collate utf8_czech_ci default null,
              `ids` varchar(32) collate utf8_czech_ci default null,
              `created` datetime NOT null,
              `modified` datetime NOT null,
              `pohoda_id` int(11) default null,
              PRIMARY KEY  (`id`),
              UNIQUE KEY `ids` (`ids`)
              ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
             */
        }
        $queryRawEnd = "\n) ENGINE=MyISAM  DEFAULT CHARSET=" . $this->Charset . ' COLLATE=' . $this->Collate . ';';
        $queryRaw = $QueryRawBegin . implode(",\n", array_merge($queryRawItems, $Indexes)) . $queryRawEnd;

        return $queryRaw;
    }

    /**
     * Vrací seznam tabulek v aktuálné použité databázi
     *
     * @param bool $sort setřídit vrácené výsledky ?
     *
     * @return array
     */
    public function listTables($sort = false)
    {
        $tablesList = array();
        foreach ($this->queryToArray('SHOW TABLES') as $tableName) {
            $tablesList[current($tableName)] = current($tableName);
        }
        if ($sort) {
            asort($tablesList, SORT_LOCALE_STRING);
        }
        return $tablesList;
    }

    /**
     * Vytvoří podle dat v objektu chybějící sloupečky v DB
     *
     * @param EaseBrick|mixed $easeBrick objekt pomocí kterého se získá struktura
     * @param array           $data      struktura sloupců k vytvoření
     *
     * @return int pocet operaci
     */
    public static function createMissingColumns(& $easeBrick, $data = null)
    {
        $Result = 0;
        $badQuery = $easeBrick->easeShared->myDbLink->getLastQuery();
        $tableColumns = $easeBrick->easeShared->myDbLink->describe($easeBrick->myTable);
        if (count($tableColumns)) {
            if (is_null($data)) {
                $data = $easeBrick->getData();
            }
            foreach ($data as $DataColumn => $DataValue) {
                if (!strlen($DataColumn)) {
                    continue;
                }
                if (!array_key_exists($DataColumn, $tableColumns[$easeBrick->myTable])) {
                    switch (gettype($DataValue)) {
                        case 'boolean':
                            $ColumnType = 'TINYINT( 1 )';
                            break;
                        case 'string':
                            if (strlen($DataValue) > 255) {
                                $ColumnType = 'TEXT';
                            } else {
                                $ColumnType = 'VARCHAR(' . strlen($DataValue) . ')';
                            }
                            break;
                        case 'integer':
                            $ColumnType = 'INT( ' . strlen($DataValue) . ' )';
                            break;
                        case 'double':
                        case 'float':
                            list($M, $D) = explode(',', str_replace('.', ',', $DataValue));
                            $ColumnType = 'FLOAT( ' . strlen($M) . ',' . strlen($D) . ' )';
                            break;

                        default:
                            continue;
                            break;
                    }
                    $AddColumnQuery = 'ALTER TABLE `' . $easeBrick->myTable . '` ADD `' . $DataColumn . '` ' . $ColumnType . ' null DEFAULT null';
                    if (!$easeBrick->myDbLink->exeQuery($AddColumnQuery)) {
                        $easeBrick->addStatusMessage($AddColumnQuery, 'error');
                        $Result--;
                    } else {
                        $easeBrick->addStatusMessage($AddColumnQuery, 'success');
                        $Result++;
                    }
                }
            }
        }
        $easeBrick->myDbLink->LastQuery = $badQuery;

        return $Result;
    }

    /**
     * Ukončí připojení k databázi
     *
     * @return type
     */
    public function close()
    {
        if (is_resource($this->SQLLink)) {
            return mysqli_close($this->SQLLink);
        } else {
            return $this->SQLLink->close();
        }
    }

    /**
     * Virtuální funkce
     *
     * @return null
     */
    public function __destruct()
    {
        return null;
    }

}

/**
 * Compatibility alias
 *
 * @author     Vitex <vitex@hippy.cz>
 * @deprecated nyní se používá EaseDbMySqli
 */
class EaseDbMySql extends EaseDbMySqli
{
    
}

class EaseDbAnsiMySQL extends EaseDbMySql
{

    /**
     * Nastavení vlastností přípojení
     * @var array
     */
    public $ConnectionSettings = array(
        'NAMES' => 'utf8',
        'GLOBAL sql_mode  = \'ANSI\'' => '',
        'GLOBAL TRANSACTION ISOLATION LEVEL SERIALIZABLE' => ''
    );

}
