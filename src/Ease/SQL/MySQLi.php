<?php
/**
 * Obsluha MySQL.
 *
 * @deprecated since version 2.0
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */

namespace Ease\SQL;

/**
 * Třída pro práci s MySQL.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class MySQLi extends SQL
{
    /**
     * MySQLi class instance.
     *
     * @var mysqli
     */
    public $sqlLink   = null; // MS SQL link identifier
    /**
     * SQLLink result.
     *
     * @var mysqli_result
     */
    public $result    = null;
    public $status    = false; //Pripojeno ?
    public $lastQuery = '';
    public $numRows   = 0;
    public $debug     = false;
    public $keyColumn = '';
    public $data      = null;
    public $charset   = 'utf8';
    public $collate   = 'utf8_czech_ci';

    /**
     * Povolit Explain každého dotazu do logu ?
     *
     * @var bool
     */
    public $explainMode = false;

    /**
     * Nastavení vlastností přípojení.
     *
     * @var array
     */
    public $connectionSettings = [
        'NAMES' => 'utf8',
    ];

    /**
     * Saves obejct instace (singleton...).
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
            $class          = __CLASS__;
            self::$instance = new $class();
        }

        return self::$instance;
    }

    /**
     * Escapes special characters in a string for use in an SQL statement.
     *
     * @param string $text
     *
     * @return string
     */
    public function addSlashes($text)
    {
        return $this->sqlLink->real_escape_string($text);
    }

    /**
     * Připojí se k mysql databázi.
     */
    public function connect()
    {
        $this->sqlLink = new \mysqli($this->server, $this->username,
            $this->password);
        if ($this->sqlLink->connect_errno) {
            $this->addStatusMessage('Connect: error #'.$this->sqlLink->connect_errno.' '.$this->sqlLink->connect_error,
                'error');

            return false;
        } else {
            if ($this->selectDB($this->database)) {
                $this->errorText = $this->sqlLink->error;
                parent::connect();
            } else {
                return false;
            }
        }
    }

    /**
     * Změní aktuálně použitou databázi.
     *
     * @param string $dbName
     *
     * @return bool
     */
    public function selectDB($dbName = null)
    {
        parent::selectDB($dbName);
        $change = $this->sqlLink->select_db($dbName);
        if ($change) {
            $this->Database = $dbName;
        } else {
            $this->errorText   = $this->sqlLink->error;
            $this->errorNumber = $this->sqlLink->errno;
            $this->addStatusMessage('Connect: error #'.$this->errorNumber.' '.$this->errorText,
                'error');
            $this->logError();
        }

        return $change;
    }

    /**
     * Vykoná QueryRaw a vrátí výsledek.
     *
     * @param string $queryRaw
     * @param bool   $ignoreErrors
     *
     * @return SQLhandle
     */
    public function exeQuery($queryRaw, $ignoreErrors = false)
    {
        $queryRaw           = $this->sanitizeQuery($queryRaw);
        $this->lastQuery    = $queryRaw;
        $this->lastInsertID = null;
        $this->errorText    = null;
        $this->errorNumber  = null;
        $sqlAction          = trim(strtolower(current(explode(' ', $queryRaw))));

        $this->result      = $this->sqlLink->query($queryRaw);
        $this->errorNumber = $this->sqlLink->errno;
        $this->errorText   = $this->sqlLink->error;

        $this->logSqlError($ignoreErrors);

        switch ($sqlAction) {
            case 'select':
            case 'show':
                if (!$this->errorText) {
                    $this->numRows = $this->result->num_rows;
                }
                break;
            case 'insert':
                if (!$this->errorText) {
                    $this->lastInsertID = $this->sqlLink->insert_id;
                }
            case 'update':
            case 'replace':
            case 'delete':
            case 'alter':
                $this->numRows = $this->sqlLink->affected_rows;
                break;
            default:
                $this->numRows = null;
        }
        if ($this->explainMode) {
            $explainQuery = $this->sqlLink->query('EXPLAIN '.$queryRaw);
            if ($explainQuery) {
                $explainedQuery = $explainQuery->fetch_assoc();
                $this->addToLog('Explain: '.$queryRaw."\n".$this->printPreBasic($explainedQuery),
                    'explain');
            }
        }

        return $this->result;
    }

    /**
     * vraci vysledek SQL dotazu $QueryRaw jako pole (uchovavane take jako $this->Resultarray).
     *
     * @param string $queryRaw
     * @param string $keyColumnToIndex umožní vrátit pole výsledků číslovaných podle $DataRow[$KeyColumnToIndex];
     *
     * @return array
     */
    public function queryToArray($queryRaw, $keyColumnToIndex = false)
    {
        $resultArray = [];
        if ($this->exeQuery($queryRaw) && is_object($this->result)) {
            if (is_string($keyColumnToIndex)) {
                while ($dataRow = $this->result->fetch_assoc()) {
                    $resultArray[$dataRow[$keyColumnToIndex]] = $dataRow;
                }
            } else {
                if (($keyColumnToIndex === true) && isset($this->myKeyColumn)) {
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
            return;
        }

        return $resultArray;
    }

    /**
     * vloží obsah pole $data do předvolené tabulky $this->myTable.
     *
     * @param string $data
     *
     * @return sqlresult
     */
    public function arrayToInsert($data)
    {
        return $this->exeQuery('INSERT INTO `'.$this->TableName.'` SET '.$this->arrayToQuery($data));
    }

    /**
     * upravi obsah zaznamu v predvolene tabulce $this->myTable, kde klicovy sloupec
     * $this->myKeyColumn je hodnota v klicovem sloupci hodnotami z pole $data.
     *
     * @param array $data  asociativní pole dat
     * @param int   $keyID id záznamu. Není li uveden použije se aktuální
     *
     * @return sqlresult
     */
    public function arrayToUpdate($data, $keyID = null)
    {
        if (!$keyID) {
            $idCol = $data[$this->keyColumn];
        }
        unset($data[$this->keyColumn]);

        return $this->exeQuery(SQL::$upd.$this->TableName.' SET '.$this->arrayToQuery($data).SQL::$whr.$this->keyColumn.'='.$idCol);
    }

    /**
     * z pole $data vytvori fragment SQL dotazu za WHERE (klicovy sloupec
     * $this->myKeyColumn je preskocen pokud neni $key false).
     *
     * @param array $data
     * @param bool  $Key
     *
     * @return string
     */
    public function arrayToQuery($data, $Key = true)
    {
        $updates = '';
        foreach ($data as $column => $value) {
            if (!strlen($column)) {
                continue;
            }
            if (($column == $this->keyColumn) && $Key) {
                continue;
            }
            switch (gettype($value)) {
                case 'integer':
                    $value = " $value ";
                    break;
                case 'float':
                case 'double':
                    $value = ' '.str_replace(',', '.', $value).' ';
                    break;
                case 'boolean':
                    if ($value) {
                        $value = ' 1 ';
                    } else {
                        $value = ' 0 ';
                    }
                    break;
                case 'null':
                    $value = ' null ';
                    break;
                case 'string':
                    if ($value != 'NOW()') {
                        if (!strstr($value, "\'")) {
                            $value = " '".str_replace("'", "\'", $value)."' ";
                        } else {
                            $value = " '$value' ";
                        }
                    }
                    break;
                default:
                    $value = " '$value' ";
            }

            $updates .= " `$column` = $value,";
        }

        return substr($updates, 0, -1);
    }

    /**
     * Generuje fragment MySQL dotazu z pole data.
     *
     * @param array  $data Pokud hodnota zacina znakem ! Je tento odstranen a generovan je negovany test
     * @param string $ldiv typ generovane podminky AND/OR
     *
     * @return sql
     */
    public function prepSelect($data, $ldiv = 'AND')
    {
        $operator     = null;
        $conditions   = [];
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
                $conditions[] = " `$column` !='' ";
                continue;
            }

            if (is_null($value)) {
                $value    = 'null';
                $operator = ' IS ';
            } else {
                if (strlen($value) && ($value[0] == '!')) {
                    $operator = ' != ';
                    $value    = substr($value, 1);
                } else {
                    if (($value == '!null') || (strtoupper($value) == 'IS NOT null')) {
                        $value    = 'null';
                        $operator = 'IS NOT';
                    } else {
                        if (is_null($operator)) {
                            $operator = ' = ';
                        }
                    }
                }
                if (is_bool($value)) {
                    if ($value === null) {
                        $value .= ' null,';
                    } elseif ($value) {
                        $value = ' 1';
                    } else {
                        $value = ' 0';
                    }
                } elseif (!is_string($value)) {
                    $value = " $value";
                } else {
                    if (strtoupper($value) == 'NOW()') {
                        $value = " 'NOW()'";
                    } else {
                        if ($value != 'null') {
                            $value = " '".addslashes($value)."'";
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

            $conditions[] = " `$column` $operator $value ";
        }

        return trim(implode($ldiv, $conditions).' '.implode(' ', $conditionsII));
    }

    /**
     * Vrací 1 pokud tabulka v databázi existuje.
     *
     * @param string $tableName
     *
     * @return int
     */
    public function tableExist($tableName = null)
    {
        if (!parent::tableExist($tableName)) {
            return;
        }
        $this->exeQuery("SHOW TABLES LIKE '".$tableName."'");
        if ($this->numRows) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vrací počet řádek v tabulce.
     *
     * @param string $tableName
     *
     * @return int
     */
    public function getTableNumRows($tableName = null)
    {
        if (!$tableName) {
            $tableName = $this->TableName;
        }
        $TableRowsCount = $this->queryToArray(SQL::$sel.'count(*) AS NumRows FROM `'.$this->easeAddSlashes($tableName).'`');

        return $TableRowsCount[0]['NumRows'];
    }

    /**
     * Vyprázdní tabulku.
     *
     * @param string $tableName
     *
     * @return bool success
     */
    public function truncateTable($tableName)
    {
        $this->exeQuery('TRUNCATE '.$tableName);
        if (!$this->getTableNumRows($tableName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Ukončí připojení k databázi.
     *
     * @return type
     */
    public function close()
    {
        if (is_resource($this->sqlLink)) {
            return mysqli_close($this->sqlLink);
        } else {
            return $this->sqlLink->close();
        }
    }

    /**
     * Virtuální funkce.
     */
    public function __destruct()
    {
        return;
    }
}
