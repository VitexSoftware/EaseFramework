<?php

/**
 * Můstek pro Pear/MD2
 *
 * @package EaseFrameWork
 * @author  Vitex <vitex@hippy.cz>
 * @copyright 2009-2014 Vitex@vitexsoftware.cz (G)
 */
require_once 'EaseSQL.php';
require_once 'MDB2.php';

/**
 * Description of EaseDB2
 *
 * @author vitex
 */
abstract class EaseDB2 extends EaseSQL {

    /**
     * Parametry připojení 
     * @var array
     */
    public $dsn = array();

    /**
     * Připojí se k mysql databázi
     * 
     * @return boolean Status připojení
     */
    public function connect() {
        $this->dsn['username'] = $this->username;
        $this->dsn['password'] = $this->password;
        $this->dsn['hostspec'] = $this->server;
        $this->dsn['database'] = $this->database;
        $this->connectionSettings['portability'] = MDB2_PORTABILITY_NONE;

        $this->sqlLink = MDB2::singleton($this->dsn, $this->connectionSettings);

        if (is_a($this->sqlLink, 'PEAR_Error')) {
            $this->addStatusMessage($this->sqlLink->getMessage(), 'error');
            return false;
        }

        $this->status = true;
        return true;
    }

    /**
     * Odstraní z SQL dotazu "nebezpečné" znaky
     *
     * @param string $queryRaw SQL Query
     *
     * @return string SQL Query
     */
    public function sanitizeQuery($queryRaw) {
        $sanitizedQuery = trim($queryRaw);

        return $sanitizedQuery;
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

        $sqlAction = trim(strtolower(current(explode(' ', $queryRaw))));
        do {
            $this->result = $this->sqlLink->query($queryRaw);

            if (is_a($this->result, 'PEAR_Error') && !$ignoreErrors) {
                $this->errorText = $this->result->getMessage();
                if (EaseShared::isCli()) {
                    if (function_exists('xdebug_call_function'))
                        echo "\nVolano tridou <b>" . xdebug_call_class() . ' v souboru ' . xdebug_call_file() . ":" . xdebug_call_line() . " funkcí " . xdebug_call_function() . "\n";
                    echo "\n$queryRaw\n\n#" .  $this->errorText;
                } else {
                    echo "<br clear=all><pre class=\"error\" style=\"border: red 1px dahed; \">";
                    if (function_exists('xdebug_print_function_stack')) {
                        xdebug_print_function_stack("Volano tridou <b>" . xdebug_call_class() . '</b> v souboru <b>' . xdebug_call_file() . ":" . xdebug_call_line() . "</b> funkci <b>" . xdebug_call_function() . '</b>');
                    }
                    echo "<br clear=all>$queryRaw\n\n<br clear=\"all\"><strong>" . $this->errorText . '</strong></pre></br>';
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
                    $this->numRows = $this->result->numRows();
                }
                break;
            case 'insert':
                if (!$this->errorText) {
                    $this->lastInsertID = $this->sqlLink->lastInsertID();
                }
            case 'update':
            case 'replace':
            case 'delete':
            case 'alter':
                $this->numRows = $this->sqlLink->_affectedRows($this->sqlLink);
                break;
            default:
                $this->numRows = null;
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
    public function queryToArray($queryRaw, $keyColumnToIndex = false) {
        $resultArray = array();
        if ($this->exeQuery($queryRaw)) {
            if (is_string($keyColumnToIndex)) {
                while ($dataRow = $this->result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                    $resultArray[$dataRow[$keyColumnToIndex]] = $dataRow;
                }
            } else {
                if (($keyColumnToIndex == true) && isset($this->myKeyColumn)) {
                    while ($dataRow = $this->result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
                        $resultArray[$dataRow[$this->myKeyColumn]] = $dataRow;
                    }
                } else {
                    while ($dataRow = $this->result->fetchRow(MDB2_FETCHMODE_ASSOC)) {
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
     * Uzavře připojení k databázi
     */
    function close(){
        $this->sqlLink->disconnect();
    }
    
}

/**
 * MySQL DB2 třída
 */
class EaseDB2MySql extends EaseDB2 {
    /**
     * MySQL Handle
     * 
     * @var MDB2_Driver_mysql
     */
    public $sqlLink = null;
    /**
     * Nastavení vlastností přípojení
     * @var array
     */
    public $ettings = array(
        'NAMES' => 'utf8'
    );

    /**
     * Saves obejct instace (singleton...)
     */
    private static $instance = null;

    /**
     * Připojí se k mysql databázi
     * 
     * @return boolean Status připojení
     */
    function connect() {
        $this->dsn['phptype'] = 'mysql';

        $this->settings = array('NAMES' => 'utf8');
        
        $status = parent::connect();
        if($status){
            $this->setUp();
        }
        return $status;
    }

    /**
     * Nastaví připojení
     * 
     */
    public function setUp()
    {
        if (!$this->connectAllreadyUP) {
            if (isset($this->settings) && is_array($this->settings) && count($this->settings)) {
                foreach ($this->settings as $setName => $setValue) {
                    if (strlen($setName)) {
                        $this->exeQuery("SET $setName $setValue");
                    }
                }
                $this->connectAllreadyUP = true;
            }
        }
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

            $updates.=" `$column` = $value,";
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

    
    
}
