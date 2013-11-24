<?php

/**
 * Obsluha MySQL
 * 
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2012 Vitex@hippy.cz (G)
 */
require_once 'EaseSQL.php';

/**
 * Třída pro práci s MySQL
 * 
 * @author Vitex <vitex@hippy.cz>
 */
class EaseDbMySqli extends EaseSql
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
    public $Result = null;
    public $status = false; //Pripojeno ?
    public $LastQuery = '';
    public $NumRows = 0;
    public $Debug = false;
    public $KeyColumn = '';
    public $Data = null;
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
    function connect()
    {
        $this->SQLLink = new mysqli($this->Server, $this->Username, $this->Password);
        if ($this->SQLLink->connect_errno) {
            $this->addStatusMessage('Connect: error #' . $this->SQLLink->connect_errno . ' ' . $this->SQLLink->connect_error, 'error');
            return FALSE;
        } else {
            if ($this->selectDB($this->Database)){
                $this->ErrorText = $this->SQLLink->error;
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
    function selectDB($DbName = null)
    {
        parent::selectDB($DbName);
        $Change = $this->SQLLink->select_db($DbName);
        if ($Change) {
            $this->Database = $DbName;
        } else {
            $this->ErrorText = $this->SQLLink->error;
            $this->ErrorNumber = $this->SQLLink->errno;
            $this->addStatusMessage('Connect: error #' . $this->ErrorNumber . ' ' . $this->ErrorText, 'error');
            $this->logError();
        }
        return $Change;
    }

    /**
     * Vykoná QueryRaw a vrátí výsledek
     * 
     * @param string $QueryRaw
     * @param boolean $IgnoreErrors
     * 
     * @return SQLhandle
     */
    function exeQuery($QueryRaw, $IgnoreErrors = false)
    {
        $QueryRaw = $this->sanitizeQuery($QueryRaw);
        $this->LastQuery = $QueryRaw;
        $this->LastInsertID = null;
        $this->ErrorText = null;
        $this->ErrorNumber = null;
        $SQLAction = trim(strtolower(current(explode(' ', $QueryRaw))));
        do {
            $this->Result = $this->SQLLink->query($QueryRaw);
            $this->ErrorNumber = $this->SQLLink->errno;
            $this->ErrorText = $this->SQLLink->error;
            if (!$this->Result && !$IgnoreErrors) {
                if (EaseShared::isCli()) {
                    if (function_exists('xdebug_call_function'))
                        echo "\nVolano tridou <b>" . xdebug_call_class() . ' v souboru ' . xdebug_call_file() . ":" . xdebug_call_line() . " funkcí " . xdebug_call_function() . "\n";
                    echo "\n$QueryRaw\n\n#" . $this->ErrorNumber . ":" . $this->ErrorText;
                } else {
                    echo "<br clear=all><pre class=\"error\" style=\"border: red 1px dahed; \">";
                    if (function_exists('xdebug_print_function_stack')) {
                        xdebug_print_function_stack("Volano tridou <b>" . xdebug_call_class() . '</b> v souboru <b>' . xdebug_call_file() . ":" . xdebug_call_line() . "</b> funkci <b>" . xdebug_call_function() . '</b>');
                    }
                    echo "<br clear=all>$QueryRaw\n\n<br clear=\"all\">#" . $this->ErrorNumber . ":<strong>" . $this->ErrorText . '</strong></pre></br>';
                }
                $this->logError();
                $this->error('ExeQuery: #' . $this->ErrorNumber . ': ' . $this->ErrorText . "\n" . $QueryRaw);
                if ($this->ErrorNumber == 2006) {
                    $this->reconnect();
                } else {
                    return null;
                }
            }
        } while ($this->ErrorNumber == 2006); // 'MySQL server has gone away'

        switch ($SQLAction) {
            case 'select':
            case 'show':
                if (!$this->ErrorText) {
                    $this->NumRows = $this->Result->num_rows;
                }
                break;
            case 'insert':
                if (!$this->ErrorText) {
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
            $EexplainQuery = $this->SQLLink->query('EXPLAIN ' . $QueryRaw);
            if ($EexplainQuery) {
                $ExplainedQuery = $EexplainQuery->fetch_assoc();
                $this->addToLog('Explain: ' . $QueryRaw . "\n" . $this->printPreBasic($ExplainedQuery), 'explain');
            }
        }
        return $this->Result;
    }

    /**
     * vraci vysledek SQL dotazu $QueryRaw jako pole (uchovavane take jako $this->Resultarray)
     *
     * @param string $QueryRaw
     * @param string $KeyColumnToIndex umožní vrátit pole výsledků číslovaných podle $DataRow[$KeyColumnToIndex];
     * 
     * @return array
     */
    function queryToArray($QueryRaw, $KeyColumnToIndex = false)
    {
        $ResultArray = array();
        if ($this->exeQuery($QueryRaw)) {
            if (is_string($KeyColumnToIndex)) {
                while ($DataRow = $this->Result->fetch_assoc()) {
                    $ResultArray[$DataRow[$KeyColumnToIndex]] = $DataRow;
                }
            } else {
                if (($KeyColumnToIndex == true) && isset($this->MyKeyColumn)) {
                    while ($DataRow = $this->Result->fetch_assoc()) {
                        $ResultArray[$DataRow[$this->MyKeyColumn]] = $DataRow;
                    }
                } else {
                    while ($DataRow = $this->Result->fetch_assoc()) {
                        $ResultArray[] = $DataRow;
                    }
                }
            }
        } else {
            return null;
        }
        return $ResultArray;
    }

    /**
     * vloží obsah pole $data do předvolené tabulky $this->MyTable
     *
     * @param string $Data
     * 
     * @return sqlresult
     */
    function arrayToInsert($data)
    {
        return $this->exeQuery('INSERT INTO `' . $this->TableName . '` SET ' . $this->arrayToQuery($data));
    }

    /**
     * upravi obsah zaznamu v predvolene tabulce $this->MyTable, kde klicovy sloupec
     * $this->MyKeyColumn je hodnota v klicovem sloupci hodnotami z pole $data
     *
     * @param array $Data  asociativní pole dat
     * @param int   $KeyID id záznamu. Není li uveden použije se aktuální
     * 
     * @return sqlresult
     *
     */
    function arrayToUpdate($Data, $KeyID = null)
    {
        if (!$KeyID) {
            $IDCol = $Data[$this->KeyColumn];
        }
        unset($Data[$this->KeyColumn]);
        return $this->exeQuery('UPDATE ' . $this->TableName . ' SET ' . $this->arrayToQuery($Data) . ' WHERE ' . $this->KeyColumn . '=' . $IDCol);
    }

    /**
     * z pole $data vytvori fragment SQL dotazu za WHERE (klicovy sloupec
     * $this->MyKeyColumn je preskocen pokud neni $key false)
     *
     * @param array $Data
     * @param boolean $Key
     * 
     * @return string
     */
    function arrayToQuery($Data, $Key = true)
    {
        $updates = '';
        foreach ($Data as $Column => $Value) {
            if (!strlen($Column)) {
                continue;
            }
            if (($Column == $this->KeyColumn) && $Key)
                continue;
            switch (gettype($Value)) {
                case 'integer':
                    $Value = " $Value ";
                    break;
                case 'float':
                case 'double':
                    $Value = ' ' . str_replace(',', '.', $Value) . ' ';
                    break;
                case 'boolean':
                    if ($Value) {
                        $Value = ' 1 ';
                    } else {
                        $Value = ' 0 ';
                    }
                    break;
                case 'NULL':
                    $Value = ' null ';
                    break;
                case 'string':
                    if ($Value != 'NOW()')
                        if (!strstr($Value, "\'")) {
                            $Value = " '" . str_replace("'", "\'", $Value) . "' ";
                        } else {
                            $Value = " '$Value' ";
                        }
                    break;
                default:
                    $Value = " '$Value' ";
            }

            $updates.=" `$Column` = $Value,";
        }
        return substr($updates, 0, -1);
    }

    /**
     * Generuje fragment MySQL dotazu z pole Data
     *
     * @param array $Data Pokud hodnota zacina znakem ! Je tento odstranen a generovan je negovany test
     * @param string $ldiv typ generovane podminky AND/OR
     * 
     * @return sql
     */
    function prepSelect($Data, $ldiv = 'AND')
    {
        $Conditions = array();
        $Conditions2 = array();
        foreach ($Data as $Column => $Value) {
            if (is_integer($Column)) {
                $Conditions2[] = $Value;
                continue;
            }
            if (($Column == $this->KeyColumn) && ($this->KeyColumn == ''))
                continue;
            if (is_string($Value) && (($Value == '!=""') || ($Value == "!=''"))) {
                $Conditions[] = " `$Column` !='' ";
                continue;
            }

            if (is_null($Value)) {
                $Value = 'null';
                $Operator = ' IS ';
            } else {
                if (strlen($Value) && ($Value[0] == '!')) {
                    $Operator = ' != ';
                    $Value = substr($Value, 1);
                } else {
                    $Operator = ' = ';
                }
                if (is_bool($Value)) {
                    if ($Value === null) {
                        $Value.=" null,";
                    } elseif ($Value) {
                        $Value = " 1";
                    } else {
                        $Value = " 0";
                    }
                } elseif (!is_string($Value)) {
                    $Value = " $Value";
                } else {
                    if (strtoupper($Value) == 'NOW()') {
                        $Value = " 'NOW()'";
                    } else {
                        $Value = " '" . addslashes($Value) . "'";
                    }
                    if ($Operator == ' != ') {
                        $Operator = ' NOT LIKE ';
                    } else {
                        $Operator = ' LIKE ';
                    }
                }
            }

            $Conditions[] = " `$Column` $Operator $Value ";
        }
        return trim(implode($ldiv, $Conditions) . ' ' . implode(' ', $Conditions2));
    }

    /**
     * Vrací strukturu tabulky jako pole
     *
     * @param string $TableName
     * 
     * @return array Struktura tabulky
     */
    function describe($TableName = null)
    {
        if (!parent::describe($TableName)) {
            return null;
        }
        foreach ($this->queryToArray("DESCRIBE $TableName") as $Column) {
            $this->TableStructure[$TableName][$Column['Field']] = $Column;
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
    function tableExist($TableName = null)
    {
        if (!parent::tableExist($TableName))
            return null;
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
    function getTableNumRows($TableName = null)
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
    function createTable(& $TableStructure = null, $TableName = null)
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
    function truncateTable($TableName)
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
     * @param bool $Primary  create Index as Primary Key
     * @param string $TableName if unset $this->TableName is used
     * 
     * @return sql handle
     */
    function addTableKey($ColumnName, $Primary = false, $TableName = null)
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
     * @param array  $TableStructure struktura tabulky
     * @param string $TableName      název tabulky
     */
    function createTableQuery(&$TableStructure, $TableName = null)
    {
        if (!$TableName) {
            $TableName = $this->TableName;
        }
        if (!parent::createTableQuery($TableStructure, $TableName)) {
            return null;
        }
        $QueryRawItems = array();
        $Indexes = array();

        $QueryRawBegin = "CREATE TABLE IF NOT EXISTS `$TableName` (\n";
        foreach ($TableStructure as $ColumnName => $ColumnProperties) {

            switch ($ColumnProperties['type']) {
                case 'bit':
                    $ColumnProperties['type'] = 'tinyint';
                    break;
                case 'money':
                case 'decimal(10,4)(19)':
                    $ColumnProperties['type'] = 'decimal(10,4)';
                    break;

                default:
                    break;
            }


            $RawItem = "  `" . $ColumnName . "` " . $ColumnProperties['type'];

            if (isset($ColumnProperties['size'])) {
                $RawItem .= '(' . $ColumnProperties['size'] . ') ';
            }

            if (array_key_exists('unsigned', $ColumnProperties) || isset($ColumnProperties['unsigned'])) {
                $RawItem .= " UNSIGNED ";
            }

            if (array_key_exists('null', $ColumnProperties)) {
                if ($ColumnProperties['null'] == true) {
                    $RawItem .= " NULL ";
                } else {
                    $RawItem .= " NOT NULL ";
                }
            }
            if (array_key_exists('ai', $ColumnProperties)) {
                $RawItem .= " AUTO_INCREMENT ";
            }




            $QueryRawItems[] = $RawItem;

            if (array_key_exists('key', $ColumnProperties) || isset($ColumnProperties['key'])) {
                if (( isset($ColumnProperties['key']) && ($ColumnProperties['key'] == 'primary')) || ( isset($ColumnProperties['Key']) && ($ColumnProperties['Key'] === 'primary') )) {
                    $Indexes[] = 'PRIMARY KEY  (`' . $ColumnName . '`)';
                } else {
                    $Indexes[] = 'KEY  (`' . $ColumnName . '`)';
                }
            }
            if (array_key_exists('ai', $ColumnProperties)) {
                $QueryRawItems[key($QueryRawItems)] .= ' AUTO_INCREMENT ';
            }
            if (array_key_exists('null', $ColumnProperties)) {
                if ($ColumnProperties['null'] == true) {
                    $QueryRawItems.= ' NULL ';
                } else {
                    $QueryRawItems.= ' NOT NULL ';
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
        $QueryRawEnd = "\n) ENGINE=MyISAM  DEFAULT CHARSET=" . $this->Charset . ' COLLATE=' . $this->Collate . ';';
        $QueryRaw = $QueryRawBegin . implode(",\n", array_merge($QueryRawItems, $Indexes)) . $QueryRawEnd;
        return $QueryRaw;
    }

    /**
     * Vrací seznam tabulek v aktuálné použité databázi
     * 
     * @param bool $Sort setřídit vrácené výsledky ?
     * 
     * @return array
     */
    function listTables($Sort = false)
    {
        $TablesList = array();
        foreach ($this->queryToArray('SHOW TABLES') as $TableName) {
            $TablesList[current($TableName)] = current($TableName);
        }
        if ($Sort) {
            asort($TablesList, SORT_LOCALE_STRING);
        }
        return $TablesList;
    }

    /**
     * Vytvoří podle dat v objektu chybějící sloupečky v DB
     * 
     * @param EaseBrick|mixed $EaseBrick objekt pomocí kterého se získá struktura
     * @param array           $Data      struktura sloupců k vytvoření
     * 
     * @return int pocet operaci
     */
    static public function createMissingColumns(& $EaseBrick, $Data = null)
    {
        $Result = 0;
        $BadQuery = $EaseBrick->EaseShared->MyDbLink->getLastQuery();
        $TableColumns = $EaseBrick->EaseShared->MyDbLink->describe($EaseBrick->MyTable);
        if (count($TableColumns)) {
            if (is_null($Data)) {
                $Data = $EaseBrick->getData();
            }
            foreach ($Data as $DataColumn => $DataValue) {
                if (!strlen($DataColumn)) {
                    continue;
                }
                if (!array_key_exists($DataColumn, $TableColumns[$EaseBrick->MyTable])) {
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
                    $AddColumnQuery = 'ALTER TABLE `' . $EaseBrick->MyTable . '` ADD `' . $DataColumn . '` ' . $ColumnType . ' null DEFAULT null';
                    if (!$EaseBrick->MyDbLink->exeQuery($AddColumnQuery)) {
                        $EaseBrick->addStatusMessage($AddColumnQuery, 'error');
                        $Result--;
                    } else {
                        $EaseBrick->addStatusMessage($AddColumnQuery, 'success');
                        $Result++;
                    }
                }
            }
        }
        $EaseBrick->MyDbLink->LastQuery = $BadQuery;
        return $Result;
    }

    /**
     * Ukončí připojení k databázi
     * 
     * @return type 
     */
    function close()
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
    function __destruct()
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

class EaseDbAnsiMySQL extends EaseDbMySql {
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

?>
