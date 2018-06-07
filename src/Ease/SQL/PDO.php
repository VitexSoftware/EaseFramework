<?php
/**
 * Obsluha SQL PDO.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2015 Vitex@hippy.cz (G)
 */

namespace Ease\SQL;

/**
 * Třída pro práci s PDO.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class PDO extends SQL
{
    /**
     * DBO class instance.
     *
     * @var DBO
     */
    public $sqlLink = null;

    /**
     * SQLLink result.
     *
     * @var PDOStatement
     */
    public $result    = null;
    public $status    = false; //Pripojeno ?
    public $lastQuery = '';
    public $numRows   = 0;
    public $debug     = false;

    /**
     * KeyColumn used for postgresql insert id.
     *
     * @var string
     */
    public $keyColumn = null;

    /**
     * Table used for postgresql insert id.
     *
     * @var string
     */
    public $myTable = null;
    public $data    = null;
    public $charset = 'utf8';
    public $collate = 'utf8_czech_ci';

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
    public $connectionSettings = [];

    /**
     * Saves obejct instace (singleton...).
     */
    private static $_instance = null;

    /**
     * Database Type.
     *
     * @var type
     */
    public $dbType = null;

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako konstruktor)
     * se bude v ramci behu programu pouzivat pouze jedna jeho instance (ta prvni).
     *
     * @link http://docs.php.net/en/language.oop5.patterns.html Dokumentace a priklad
     */
    public static function singleton($options = [])
    {
        if (!isset(self::$_instance)) {
            $class           = __CLASS__;
            self::$_instance = new $class($options);
        }

        return self::$_instance;
    }

    /**
     * Set KeyColumn used for PGSQL indertid.
     *
     * @param string $column
     */
    public function setKeyColumn($column = null)
    {
        if (!is_null($column)) {
            $this->keyColumn = $column;
        }
    }

    /**
     * Set Table used for PGSQL indertid.
     *
     * @param string $tablename
     */
    public function setTableName($tablename = null)
    {
        if (!empty($tablename)) {
            $this->myTable = $tablename;
        }
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
        if (isset($this->sqlLink) && method_exists($this->sqlLink,
                'real_escape_string')) {
            $slashed = $this->sqlLink->real_escape_string($text);
        } else {
            $slashed = addslashes($text);
        }

        return $slashed;
    }

    /**
     * Perform connect to database.
     *
     * @return false|null connecting result true: connected, false: error, null: no SQL configured
     */
    public function connect()
    {
        if (is_null($this->dbType)) {
            $result = null;
        } else {
            switch ($this->dbType) {
                case 'mysql':
                    $this->sqlLink = new \PDO($this->dbType.':dbname='.$this->database.';host='.$this->server.';port='.$this->port.';charset=utf8',
                        $this->username, $this->password,
                        [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'']);
                    break;
                case 'pgsql':
                    $this->sqlLink = new \PDO($this->dbType.':dbname='.$this->database.';host='.$this->server.';port='.$this->port,
                        $this->username, $this->password);
                    if (is_object($this->sqlLink)) {
                        $this->sqlLink->query("SET NAMES 'UTF-8'");
                    }
                    break;

                default:
                    throw new \Ease\Exception(_('Unimplemented Database type').': '.$this->dbType);
                    break;
            }

            if (is_object($this->sqlLink)) {
                $this->errorNumber = $this->sqlLink->errorCode();
                $this->errorText   = $this->sqlLink->errorInfo();
            } else {
                $result = false;
            }
            if ($this->errorNumber != '00000') {
                $this->addStatusMessage('Connect: error #'.$this->errorNumer.' '.$this->errorText,
                    'error');

                $result = false;
            } else {
                $result = parent::connect();
            }
        }

        return $result;
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
        if (!isset($this->sqlLink) || is_null($this->sqlLink)) {
            $this->connect();
        }

        $queryRaw           = $this->sanitizeQuery($queryRaw);
        $this->lastQuery    = $queryRaw;
        $this->lastInsertID = null;
        $this->errorText    = null;
        $this->errorNumber  = null;
        $sqlAction          = trim(strtolower(current(explode(' ', $queryRaw))));

        switch ($sqlAction) {
            case 'select':
            case 'truncate':
            case 'show':
                $this->result      = $this->sqlLink->query($queryRaw);
                $this->errorNumber = $this->sqlLink->errorCode();
                $errorText         = $this->sqlLink->errorInfo();
                if ($this->errorNumber) {
                    $this->errorText = $errorText[2];
                }
                $this->logSqlError($ignoreErrors);

                if ($this->errorNumber == '00000') {
                    $this->numRows = $this->result->rowCount();
                }
                break;
            case 'insert':
                if (($this->dbType == 'pgsql') && !strstr(strtoupper($queryRaw),
                        'ON CONFLICT')) {
                    $queryRaw .= ' RETURNING '.$this->keyColumn;
                }
            case 'replace':
            case 'delete':
                $stmt              = $this->sqlLink->prepare($queryRaw);
                $stmt->execute();
                $this->errorNumber = $this->sqlLink->errorCode();
                $this->errorText   = $this->sqlLink->errorInfo();

                if (isset($this->errorText[2])) {
                    $this->addStatusMessage($this->errorText[2].': '.$queryRaw,
                        'error');
                }

                if ($this->errorText[0] == '0000') {
                    if (($this->dbType == 'pgsql') && (strstr($queryRaw,
                            strtoupper('RETURNING')))) {
                        $fetched = $stmt->fetch();
                        if (is_array($fetched)) {
                            $this->lastInsertID = current($fetched);
                        }
                    } else {
                        $this->lastInsertID = $this->getlastInsertID();
                    }
                    $this->numRows = $stmt->rowCount();
                    $this->result  = true;
                }
                break;
            case 'update':
                $stmt              = $this->sqlLink->prepare($queryRaw);
                $stmt->execute();
                $this->errorNumber = $this->sqlLink->errorCode();
                $errorText         = $this->sqlLink->errorInfo();
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
     * Poslední genrované ID.
     *
     * @return int ID
     */
    public function getlastInsertID($column = null)
    {
        switch ($this->dbType) {
            case 'pgsql':
                if (is_null($column)) {
                    $column = $this->myTable.'_'.$this->keyColumn.'_seq';
                } else {
                    $column = $this->myTable.'_'.$column.'_seq';
                }
                break;

            default:
                break;
        }

        return $this->sqlLink->lastInsertId($column);
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
                foreach ($this->result->fetchAll(\PDO::FETCH_ASSOC) as $dataRow) {
                    $resultArray[$dataRow[$keyColumnToIndex]] = $dataRow;
                }
            } else {
                if (($keyColumnToIndex === true) && isset($this->keyColumn)) {
                    foreach ($this->result->fetchAll(\PDO::FETCH_ASSOC) as $dataRow) {
                        $resultArray[$dataRow[$this->keyColumn]] = $dataRow;
                    }
                } else {
                    foreach ($this->result->fetchAll(\PDO::FETCH_ASSOC) as $dataRow) {
                        $resultArray[] = $dataRow;
                    }
                }
            }
        } else {
            return;
        }
        $this->result->closeCursor();

        return $resultArray;
    }

    /**
     * Insert array values as new database row
     *
     * @param string $data
     *
     * @return sqlresult
     */
    public function arrayToInsert($data)
    {
        $cc  = $this->getColumnComma();
        $set = ' ';
        if ($this->dbType == 'mysql') {
            $set = ' SET ';
        }
        return $this->exeQuery('INSERT INTO '.$cc.$this->myTable.$cc.$set.$this->arrayToQuery($data));
    }

    /**
     * upravi obsah zaznamu v predvolene tabulce $this->myTable, kde klicovy sloupec
     * $this->keyColumn je hodnota v klicovem sloupci hodnotami z pole $data.
     *
     * @param array $data  asociativní pole dat
     * @param int   $KeyID id záznamu. Není li uveden použije se aktuální
     *
     * @return sqlresult
     */
    public function arrayToUpdate($data, $KeyID = null)
    {
        if (empty($KeyID)) {
            $IDCol = $data[$this->keyColumn];
        }
        unset($data[$this->keyColumn]);

        return $this->exeQuery(self::$upd.$this->myTable.' SET '.$this->arrayToQuery($data).SQL::$whr.$this->keyColumn.'='.$IDCol);
    }

    /**
     * z pole $data vytvori fragment SQL dotazu za WHERE (klicovy sloupec
     * $this->keyColumn je preskocen pokud neni $key false).
     *
     * @param array $data
     * @param bool  $key
     *
     * @return string
     */
    public function arrayToQuery($data, $key = true)
    {
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
     * $this->keyColumn je preskocen pokud neni $key false).
     *
     * @param array $data
     * @param bool  $key
     *
     * @return string
     */
    public function arrayToInsertQuery($data, $key = true)
    {
        switch ($this->dbType) {
            case 'mysql':
                $fragment = ' SET '.$this->arrayToSetQuery($data, $key);
                break;
            default:
                $fragment = $this->arrayToValuesQuery($data, $key);
                break;
        }

        return $fragment;
    }

    /**
     * z pole $data vytvori fragment SQL dotazu za WHERE (klicovy sloupec
     * $this->keyColumn je preskocen pokud neni $key false).
     *
     * @param array $data
     * @param bool  $key
     *
     * @return string
     */
    public function arrayToValuesQuery($data, $key = true)
    {
        $values = [];
        $query  = '';

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
                    $value = ' '.str_replace(',', '.', $value).' ';
                    break;
                case 'boolean':
                    if ($value) {
                        $value = ' TRUE ';
                    } else {
                        $value = ' FALSE ';
                    }
                    break;
                case 'null':
                case 'NULL':
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

            $values[$column] = "$value";
        }

        $keys = [];
        $cc   = $this->getColumnComma();
        foreach (array_keys($values) as $columnKey) {
            $keys[] = $cc.$columnKey.$cc;
        }
        $query .= '('.implode(',', $keys).') VALUES ('.implode(',', $values).') ';

        return $query;
    }

    /**
     * Generuje fragment MySQL dotazu z pole data.
     *
     * @param array  $data Pokud hodnota zacina znakem ! Je tento odstranen a generovan je negovany test
     * @param string $ldiv typ generovane podminky AND/OR
     *
     * @return string
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
                $conditions[] = ' '.$this->getColumnComa().$column.$this->getColumnComma()." !='' ";
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
                    if (($value === '!null') || (strtoupper($value) === 'IS NOT NULL')) {
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
                        $value = ' TRUE';
                    } else {
                        $value = ' FALSE';
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

            $conditions[] = ' '.$this->getColumnComma().$column.$this->getColumnComma()."$operator $value ";
        }

        return trim(implode($ldiv, $conditions).' '.implode(' ', $conditionsII));
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
        if (empty($tableName)) {
            $tableName = $this->myTable;
        }
        $tableRowsCount = $this->queryToArray(SQL::$sel.
            'count(*) AS NumRows'.SQL::$frm.$this->getColumnComma().
            $this->addSlashes($tableName).$this->getColumnComma());

        return $tableRowsCount[0]['NumRows'];
    }

    /**
     * Vrací uvozovky pro označení sloupečků.
     *
     * @return string
     */
    public function getColumnComma()
    {
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
     * Set Up PDO for curent usage.
     *
     * @param \Ease\Brick $object or its child
     */
    public function useObject($object)
    {
        $this->setKeyColumn($object->getKeyColumn());
        $this->setTableName($object->getMyTable());
    }

    /**
     * Ukončí připojení k databázi.
     *
     * @return type
     */
    public function close()
    {
        return $this->sqlLink = null;
    }

    /**
     * Virtuální funkce.
     */
    public function __destruct()
    {
        return;
    }

    /**
     * You cannot serialize or unserialize PDO instance.
     *
     * @return array fields to serialize
     */
    public function __sleep()
    {
        unset($this->sqlLink);
        unset($this->result);

        return parent::__sleep();
    }

    public function __wakeup()
    {
        $this->setUp();
}
}
