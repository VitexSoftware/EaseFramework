<?php

/**
 * Obsluha SQL PDO
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2015 Vitex@hippy.cz (G)
 */

namespace Ease\SQL;

/**
 * Třída pro práci s PDO
 *
 * @author Vitex <vitex@hippy.cz>
 */
class PDO extends SQL {

    /**
     * DBO class instance
     * @var DBO
     */
    public $sqlLink = null;

    /**
     * SQLLink result
     * @var PDOStatement
     */
    public $result = null;
    public $status = false; //Pripojeno ?
    public $lastQuery = '';
    public $numRows = 0;
    public $debug = false;

    /**
     * KeyColumn used for postgresql insert id
     * @var string
     */
    public $keyColumn = null;

    /**
     * Table used for postgresql insert id
     * @var string
     */
    public $myTable = null;
    public $data = null;
    public $charset = 'utf8';
    public $collate = 'utf8_czech_ci';

    /**
     * Povolit Explain každého dotazu do logu ?
     * @var bool
     */
    public $explainMode = false;

    /**
     * Nastavení vlastností přípojení
     * @var array
     */
    public $connectionSettings = [];

    /**
     * Saves obejct instace (singleton...)
     */
    private static $instance = null;

    /**
     * Database Type
     * @var type
     */
    public $dbType = null;

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako konstruktor)
     * se bude v ramci behu programu pouzivat pouze jedna jeho instance (ta prvni).
     *
     * @link http://docs.php.net/en/language.oop5.patterns.html Dokumentace a priklad
     */
    public static function singleton() {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class();
        }

        return self::$instance;
    }

    /**
     * Set KeyColumn used for PGSQL indertid
     *
     * @param string $column
     */
    public function setKeyColumn($column = null) {
        if (!is_null($column)) {
            $this->keyColumn = $column;
        }
//        $this->sqlLink->setKeyColumn($this->myKeyColumn);
    }

    /**
     * Set Table used for PGSQL indertid
     *
     * @param string $tablename
     */
    public function setTableName($tablename = null) {
        if ($tablename) {
            $this->myTable = $tablename;
        }
//        $this->sqlLink->setTableName($this->myTable);
    }

    /**
     * Escapes special characters in a string for use in an SQL statement
     *
     * @param string $text
     *
     * @return string
     */
    public function addSlashes($text) {
        return $this->sqlLink->real_escape_string($text);
    }

    /**
     * Připojí se k mysql databázi
     */
    public function connect() {
        switch ($this->dbType) {
            case 'mysql':
                $this->sqlLink = new \PDO($this->dbType . ':dbname=' . $this->database . ';host=' . $this->server . ';port=' . $this->port . ';charset=utf8', $this->username, $this->password, [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'']);
                break;
            case 'pgsql':
                $this->sqlLink = new \PDO($this->dbType . ':dbname=' . $this->database . ';host=' . $this->server . ';port=' . $this->port, $this->username, $this->password);
                if (is_object($this->sqlLink)) {
                    $this->sqlLink->query("SET NAMES 'UTF-8'");
                }
                break;

            default:
                //TODO: Implement Other DB's
                break;
        }

        if (is_object($this->sqlLink)) {
            $this->errorNumber = $this->sqlLink->errorCode();
            $this->errorText = $this->sqlLink->errorInfo();
        } else {
            return false;
        }
        if ($this->errorNumber != '00000') {
            $this->addStatusMessage('Connect: error #' . $this->errorNumer . ' ' . $this->errorText, 'error');
            return FALSE;
        } else {
            return parent::connect();
        }
    }

    /**
     * Změní aktuálně použitou databázi
     *
     * @param string $dbName
     *
     * @return boolean
     */
    public function selectDB($dbName = null) {
        parent::selectDB($dbName);
        $change = $this->sqlLink->select_db($dbName);
        if ($change) {
            $this->Database = $dbName;
        } else {
            $this->errorText = $this->sqlLink->error;
            $this->errorNumber = $this->sqlLink->errno;
            $this->addStatusMessage('Connect: error #' . $this->errorNumber . ' ' . $this->errorText, 'error');
            $this->logError();
        }

        return $change;
    }

    /**
     * Vykoná QueryRaw a vrátí výsledek
     *
     * @param string  $queryRaw
     * @param boolean $ignoreErrors
     *
     * @return SQLhandle
     */
    public function exeQuery($queryRaw, $ignoreErrors = false) {
        $queryRaw = $this->sanitizeQuery($queryRaw);
        $this->lastQuery = $queryRaw;
        $this->lastInsertID = null;
        $this->errorText = null;
        $this->errorNumber = null;
        $sqlAction = trim(strtolower(current(explode(' ', $queryRaw))));

        switch ($sqlAction) {
            case 'select':
            case 'show':

                $this->result = $this->sqlLink->query($queryRaw);
                $this->errorNumber = $this->sqlLink->errorCode();
                $errorText = $this->sqlLink->errorInfo();
                if ($this->errorNumber) {
                    $this->errorText = $errorText[2];
                }
                if (!$this->result && !$ignoreErrors) {
                    if (\Ease\Shared::isCli()) {
                        if (function_exists('xdebug_call_function')) {
                            echo "\nVolano tridou <b>" . xdebug_call_class() . ' v souboru ' . xdebug_call_file() . ":" . xdebug_call_line() . " funkcí " . xdebug_call_function() . "\n";
                        }
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
                }


                if ($this->errorNumber == '00000') {
                    $this->numRows = $this->result->rowCount();
                }
                break;
            case 'insert':
            case 'replace':
            case 'delete':
                $stmt = $this->sqlLink->prepare($queryRaw);
                $stmt->execute();
                $this->errorNumber = $this->sqlLink->errorCode();
                $this->errorText = $this->sqlLink->errorInfo();

                if (isset($this->errorText[2])) {
                    $this->error($this->errorText[2], $queryRaw);
                }

                if ($this->errorText[0] == '0000') {
                    $this->lastInsertID = $this->getlastInsertID();
                    $this->numRows = $stmt->rowCount();
                }
                break;
            case 'update':
                $stmt = $this->sqlLink->prepare($queryRaw);
                $stmt->execute();
                $this->errorNumber = $this->sqlLink->errorCode();
                $errorText = $this->sqlLink->errorInfo();
                if ($this->errorNumber) {
                    $this->errorText = $errorText[2];
                }
                $this->numRows = $stmt->rowCount();
                if (is_null($this->result)) {
                    $this->result = true;
                }
                break;
            case 'alter':
                $this->numRows = $stmt->rowCount();
                break;
            default:
                $this->numRows = null;
        }

        return $this->result;
    }

    /**
     * Poslední genrované ID
     *
     * @return int ID
     */
    public function getlastInsertID($column = null) {
        switch ($this->dbType) {
            case 'pgsql':
                if (is_null($column)) {
                    $column = $this->myTable . '_' . $this->myKeyColumn . '_seq';
                } else {
                    $column = $this->myTable . '_' . $column . '_seq';
                }
                break;

            default:
                break;
        }

        return $this->sqlLink->lastInsertId($column);
    }

    /**
     * vraci vysledek SQL dotazu $QueryRaw jako pole (uchovavane take jako $this->Resultarray)
     *
     * @param string $queryRaw
     * @param string $keyColumnToIndex umožní vrátit pole výsledků číslovaných podle $DataRow[$KeyColumnToIndex];
     *
     * @return array
     */
    public function queryToArray($queryRaw, $keyColumnToIndex = false) {
        $resultArray = [];
        if ($this->exeQuery($queryRaw) && is_object($this->result)) {
            if (is_string($keyColumnToIndex)) {
                foreach ($this->result->fetchAll(\PDO::FETCH_ASSOC) as $dataRow) {
                    $resultArray[$dataRow[$keyColumnToIndex]] = $dataRow;
                }
            } else {
                if (($keyColumnToIndex == true) && isset($this->myKeyColumn)) {
                    foreach ($this->result->fetchAll(\PDO::FETCH_ASSOC) as $dataRow) {
                        $resultArray[$dataRow[$this->myKeyColumn]] = $dataRow;
                    }
                } else {
                    foreach ($this->result->fetchAll(\PDO::FETCH_ASSOC) as $dataRow) {
                        $resultArray[] = $dataRow;
                    }
                }
            }
        } else {
            return null;
        }
        $this->result->closeCursor();
        return $resultArray;
    }

    /**
     * vloží obsah pole $data do předvolené tabulky $this->myTable
     *
     * @param string $data
     *
     * @return sqlresult
     */
    public function arrayToInsert($data) {
        return $this->exeQuery('INSERT INTO ' . $this->getColumnComma() . $this->TableName . $this->getColumnComma() . ' SET ' . $this->arrayToQuery($data));
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
    public function arrayToUpdate($data, $KeyID = null) {
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
     * @param boolean $key
     *
     * @return string
     */
    function arrayToQuery($data, $key = true) {
        switch ($this->dbType) {
            case 'pgsql':
                $fragment = $this->arrayToValuesQuery($data, $key);
                break;
            default:
                $fragment = $this->arrayToSetQuery($data, $key);
                break;
        }
        return $fragment;
    }

    /**
     * z pole $data vytvori fragment SQL dotazu pro INSERT (klicovy sloupec
     * $this->myKeyColumn je preskocen pokud neni $key false)
     *
     * @param array   $data
     * @param boolean $key
     *
     * @return string
     */
    function arrayToInsertQuery($data, $key = true) {
        switch ($this->dbType) {
            case 'mysql':
                $fragment = ' SET ' . $this->arrayToSetQuery($data, $key);
                break;
            default:
                $fragment = $this->arrayToValuesQuery($data, $key);
                break;
        }
        return $fragment;
    }

    /**
     * z pole $data vytvori fragment SQL dotazu za WHERE (klicovy sloupec
     * $this->myKeyColumn je preskocen pokud neni $key false)
     *
     * @param array   $data
     * @param boolean $key
     *
     * @return string
     */
    public function arrayToValuesQuery($data, $key = true) {
        $values = [];
        $query = '';

        foreach ($data as $column => $value) {
            if (!strlen($column)) {
                continue;
            }
            if (($column == $this->keyColumn) && $key) {
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
                    if ($value != 'NOW()') {
                        if (!strstr($value, "\'")) {
                            $value = " '" . str_replace("'", "\'", $value) . "' ";
                        } else {
                            $value = " '$value' ";
                        }
                    }
                    break;
                default:
                    $value = " '$value' ";
            }

            $values[$column] = "$value";
        }

        $keys = [];
        $cc = $this->getColumnComma();
        foreach (array_keys($values) as $columnKey) {
            $keys[] = $cc . $columnKey . $cc;
        }
        $query .= '(' . implode(',', $keys) . ') VALUES (' . implode(',', $values) . ') ';

        return $query;
    }

    /**
     * z pole $data vytvori fragment SQL dotazu za WHERE (klicovy sloupec
     * $this->myKeyColumn je preskocen pokud neni $key false)
     *
     * @param array   $data
     * @param boolean $key
     *
     * @return string
     */
    public function arrayToSetQuery($data, $key = true) {
        $updates = '';
        foreach ($data as $column => $value) {
            if (!strlen($column)) {
                continue;
            }
            if (($column == $this->keyColumn) && $key) {
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
                    if ($value != 'NOW()') {
                        if (!strstr($value, "\'")) {
                            $value = " '" . str_replace("'", "\'", $value) . "' ";
                        } else {
                            $value = " '$value' ";
                        }
                    }
                    break;
                default:
                    $value = " '$value' ";
            }

            $updates .= ' ' . $this->getColumnComma() . $column . $this->getColumnComma() . " = $value,";
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
    public function prepSelect($data, $ldiv = 'AND') {
        $operator = null;
        $conditions = [];
        $conditionsII = [];
        foreach ($data as $column => $value) {
            if (is_integer($column)) {
                $conditionsII[] = $value;
                continue;
            }
            if (($column == $this->keyColumn) && ($this->keyColumn == '')) {
                continue;
            }
            if (is_string($value) && (($value == '!=""') || ($value == "!=''"))) {
                $conditions[] = ' ' . $this->getColumnComa() . $column . $this->getColumnComma() . " !='' ";
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
                        if (is_null($operator)) {
                            $operator = ' = ';
                        }
                    }
                }
                if (is_bool($value)) {
                    if ($value === null) {
                        $value .= " null,";
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
                        if (is_null($operator)) {
                            $operator = ' LIKE ';
                        }
                    }
                }
            }

            $conditions[] = " " . $this->getColumnComma() . $column . $this->getColumnComma() . "$operator $value ";
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
    public function describe($tableName = null) {
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
     * @param string $tableName
     *
     * @return int
     */
    public function tableExist($tableName = null) {
        if (!parent::tableExist($tableName)) {
            return null;
        }
        $this->exeQuery("SHOW TABLES LIKE '" . $tableName . "'");
        if ($this->numRows) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vrací počet řádek v tabulce
     *
     * @param string $tableName
     *
     * @return int
     */
    public function getTableNumRows($tableName = null) {
        if (!$tableName) {
            $tableName = $this->TableName;
        }
        $TableRowsCount = $this->queryToArray('SELECT count(*) AS NumRows FROM ' . $this->getColumnComma() . $this->easeAddSlashes($tableName) . $this->getColumnComma());

        return $TableRowsCount[0]['NumRows'];
    }

    /**
     * Vytvoří tabulku podle struktůry
     *
     * @param array  $tableStructure struktura SQL
     * @param string $tableName      název tabulky
     */
    public function createTable(& $tableStructure = null, $tableName = null) {
        if (!parent::createTable($tableStructure, $tableName)) {
            return null;
        }
        if ($this->exeQuery($this->createTableQuery($tableStructure, $tableName))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vyprázdní tabulku
     *
     * @param string $tableName
     *
     * @return boolean success
     */
    public function truncateTable($tableName) {
        $this->exeQuery('TRUNCATE ' . $tableName);
        if (!$this->getTableNumRows($tableName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vytvoří index na tabulkou
     *
     * @param string $columnName
     * @param bool   $primary    create Index as Primary Key
     * @param string $tableName  if unset $this->TableName is used
     *
     * @return sql handle
     */
    public function addTableKey($columnName, $primary = false, $tableName = null) {
        if (!$tableName) {
            $tableName = $this->TableName;
        }
        if ($primary) {
            return $this->exeQuery("ALTER TABLE `$tableName` ADD PRIMARY KEY ( `$columnName` )");
        } else {
            return $this->exeQuery("ALTER TABLE `$tableName` ADD KEY ( `$columnName` )");
        }
    }

    /**
     * Vytvoří tabulku podle struktůry
     *
     * @param array  $tableStructure struktura tabulky
     * @param string $tableName      název tabulky
     */
    public function createTableQuery(&$tableStructure, $tableName = null) {
        if (!$tableName) {
            $tableName = $this->TableName;
        }
        if (!parent::createTableQuery($tableStructure, $tableName)) {
            return null;
        }
        $queryRawItems = [];
        $Indexes = [];

        $queryRawBegin = "CREATE TABLE IF NOT EXISTS `$tableName` (\n";
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
                    $queryRawItems .= ' NULL ';
                } else {
                    $queryRawItems .= ' NOT NULL ';
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
        $queryRawEnd = "\n) ENGINE=MyISAM  DEFAULT CHARSET=" . $this->charset . ' COLLATE=' . $this->collate . ';';
        $queryRaw = $queryRawBegin . implode(",\n", array_merge($queryRawItems, $Indexes)) . $queryRawEnd;

        return $queryRaw;
    }

    /**
     * Vrací seznam tabulek v aktuálné použité databázi
     *
     * @param bool $sort setřídit vrácené výsledky ?
     *
     * @return array
     */
    public function listTables($sort = false) {
        $tablesList = [];
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
    public static function createMissingColumns(& $easeBrick, $data = null) {
        $result = 0;
        $badQuery = $easeBrick->easeShared->dblink->getLastQuery();
        $tableColumns = $easeBrick->easeShared->dblink->describe($easeBrick->myTable);
        if (count($tableColumns)) {
            if (is_null($data)) {
                $data = $easeBrick->getData();
            }
            foreach ($data as $dataColumn => $dataValue) {
                if (!strlen($dataColumn)) {
                    continue;
                }
                if (!array_key_exists($dataColumn, $tableColumns[$easeBrick->myTable])) {
                    switch (gettype($dataValue)) {
                        case 'boolean':
                            $columnType = 'TINYINT( 1 )';
                            break;
                        case 'string':
                            if (strlen($dataValue) > 255) {
                                $columnType = 'TEXT';
                            } else {
                                $columnType = 'VARCHAR(' . strlen($dataValue) . ')';
                            }
                            break;
                        case 'integer':
                            $columnType = 'INT( ' . strlen($dataValue) . ' )';
                            break;
                        case 'double':
                        case 'float':
                            list($m, $d) = explode(',', str_replace('.', ',', $dataValue));
                            $columnType = 'FLOAT( ' . strlen($m) . ',' . strlen($d) . ' )';
                            break;

                        default:
                            continue;
                            break;
                    }
                    $AddColumnQuery = 'ALTER TABLE `' . $easeBrick->myTable . '` ADD `' . $dataColumn . '` ' . $columnType . ' null DEFAULT null';
                    if (!$easeBrick->dblink->exeQuery($AddColumnQuery)) {
                        $easeBrick->addStatusMessage($AddColumnQuery, 'error');
                        $result--;
                    } else {
                        $easeBrick->addStatusMessage($AddColumnQuery, 'success');
                        $result++;
                    }
                }
            }
        }
        $easeBrick->dblink->lastQuery = $badQuery;

        return $result;
    }

    /**
     * Vrací uvozovky pro označení sloupečků
     *
     * @return string
     */
    function getColumnComma() {
        switch ($this->dbType) {
            case 'pgsql':
                $coma = '"';
                break;
            case 'mysql':
                $coma = '`';
                break;
            default:
                $coma = parent::getColumnComma();
                break;
        }
        return $coma;
    }

    /**
     * Set Up PDO for curent usage
     * 
     * @param \Ease\Brick $object or its child
     */
    function useObject($object) {
        $this->setKeyColumn($object->getmyKeyColumn());
        $this->setTableName($object->getMyTable());
    }

    /**
     * Ukončí připojení k databázi
     *
     * @return type
     */
    public function close() {
        if (is_resource($this->sqlLink)) {
            return mysqli_close($this->sqlLink);
        } else {
            return $this->sqlLink->close();
        }
    }

    /**
     * Virtuální funkce
     *
     * @return null
     */
    public function __destruct() {
        return null;
    }

    /**
     * You cannot serialize or unserialize PDO instance
     *
     * @return array fields to serialize
     */
    public function __sleep() {
        unset($this->sqlLink);
        unset($this->result);
        return parent::__sleep();
    }

}
