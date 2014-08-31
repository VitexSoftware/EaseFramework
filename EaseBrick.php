<?php

/**
 * Základní objekt pracující s databázemi
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */

require_once 'Ease/EaseSand.php';
require_once 'Ease/EaseDB2.php';

/**
 * Základní objekt pracující s databázemi
 *
 * @package EaseFrameWork
 * @author  Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
class EaseBrick extends EaseSand
{

    /**
     * Objekt pro práci s MySQL
     * @var EaseDbMySqli
     */
    public $myDbLink = null;

    /**
     * Objekt pro práci s MSSQL
     * @var EaseDbMSSql
     */
    public $msDbLink = null;

    /**
     * Předvolená tabulka v MSSQL (součást identity)
     * @var string
     */
    public $msTable = '';

    /**
     * Předvolená tabulka v MySQL (součást identity)
     * @var string
     */
    public $myTable = '';

    /**
     * Sql Struktura databáze. Je obsažena ve dvou podpolích $SqlStruct['ms'] a $SqlStruct['my']
     * @var array
     */
    public $sqlStruct = null;

    /**
     * Počáteční způsob přístupu k MSSQL
     * @var string online||offline
     */
    public $msSQLMode = 'online';

    /**
     * Funkční sloupečky pro MS
     * @var array
     */
    public $msDbRoles = null;

    /**
     * Funkční sloupečky pro My
     * @var array
     */
    public $myDbRoles = null;

    /**
     * Odkaz na objekt uživatele
     * @var EaseUser | EaseAnonym
     */
    public $user = null;

    /**
     * [Cs]Základní objekt pracující s databází
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->msTable) {
            $this->msSqlUp();
        }

        if ($this->myTable) {
            $this->takemyTable($this->myTable);
        }
        
        $this->saveObjectIdentity();
    }

    /**
     * Nastavuje jméno objektu
     * Je li znnámý, doplní jméno objektu hodnotu klíče např EaseUser#vitex
     * nebo EaseProductInCart#4542
     *
     * @param string $objectName
     *
     * @return string new name
     */
    public function setObjectName($objectName = null)
    {
        if ($objectName) {
            return parent::setObjectName($objectName);
        } else {
            $key = $this->getMyKey($this->data);
            if ($key) {
                return parent::setObjectName(get_class($this) . '@' . $key);
            } else {
                return parent::setObjectName();
            }
        }
    }

    /**
     * Nastavi identitu objektu a jeho SQL Objektů
     *
     * @param array $newIdentity
     *
     * @return int Počet provedených změn
     */
    public function setObjectIdentity($newIdentity)
    {
        $changes = parent::SetObjectIdentity($newIdentity);
        if ($this->myTable) {
            $this->mySqlUp();
        }
        if ($this->msTable) {
            $this->msSqlUp();
        }

        return $changes;
    }

    /**
     * Přiřadí objektu odkaz na objekt uživatele
     *
     * @param object|EaseUser $user         pointer to user object
     * @param object          $targetObject objekt kterému je uživatel
     *                                      přiřazován.
     *
     * @return boolean
     */
    public function setUpUser(& $user, & $targetObject = null)
    {
        if (is_object($user)) {
            if (is_object($targetObject)) {
                $targetObject->user = & $user;
            } else {
                $this->user = & $user;
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * Vraci objekt uzivatele
     *
     * @return EaseUser
     */
    public function &getUser()
    {
        if (isset($this->user)) {
            $User = &$this->user;
        } else {
            if (isset($this->easeShared->User)) {
                $User = &$this->easeShared->User;
            } else {
                $User = null;
            }
        }

        return $User;
    }

    /**
     * Přidá zprávu do zásobníku pro zobrazení uživateli
     *
     * @param string  $message  zprava
     * @param string  $type     Fronta zprav (warning|info|error|success)
     * @param boolean $addIcons prida UTF8 ikonky na zacatek zprav
     * @param boolean $addToLog zapisovat zpravu do logu ?
     */
    public function addStatusMessage($message, $type = 'info', $addIcons = true, $addToLog = true)
    {
        if ($addIcons) {
            switch ($type) {
                case 'mail':                    // Obalka
                    $message = ' ✉ ' . $message;
                    break;
                case 'warning':                    // Vykřičník v trojůhelníku
                    $message = ' ⚠ ' . $message;
                    break;
                case 'error':                      // Lebka
                    $message = ' ☠ ' . $message;
                    break;
                case 'success':                    // Kytička
                    $message = ' ❁ ' . $message;
                    break;
                default:                           // i v kroužku
                    $message = ' ⓘ ' . $message;
                    break;
            }
        }
        if ($addToLog) {
            $this->addToLog($message, $type);
        }

        return parent::addStatusMessage($message, $type);
    }

    /**
     * Funkce pro defaultní slashování v celém projektu
     *
     * @param string $text text k olomítkování
     *
     * @return string
     */
    public function easeAddSlashes($text)
    {
        if (is_object($this->myDbLink) && is_resource($this->myDbLink->sqlLink)) {
            return mysql_real_escape_string($text, $this->myDbLink->sqlLink);
        } else {
            return parent::EaseAddSlashes($text);
        }
    }

    /**
     * Načte z MSSQL sloupečky podle podmínek
     *
     * @param array        $columnsList sloupce k načtení
     * @param array        $conditions  podmínky výběru
     * @param array|string $orderBy     třídit dle
     * @param array        $indexBy     klíče výsledku naplnit hodnotou ze
     *                                  sloupečku
     *
     * @return array
     */
    public function getColumnsFromMSSQL($columnsList, $conditions, $orderBy = null, $indexBy = null)
    {
        $Where = '';
        if (!is_object($this->msDbLink)) {
            $this->msSqlUp();
        }

        if (!count($columnsList)) {
            $this->error('GetColumnsFromMSSQL: Missing ColumnList');

            return null;
        }

        if (is_array($conditions)) {
            if (!count($conditions)) {
                $this->error('GetColumnsFromMSSQL: Missing Conditions');

                return null;
            }
            $Where = ' WHERE ' . $this->msDbLink->prepSelect($conditions);
        } else {
            if ($conditions) {
                $Where = ' WHERE ' . $conditions;
            }
        }

        if (is_array($indexBy)) {
            $indexBy = implode(',', $indexBy);
        }
        if ($orderBy) {
            if (is_array($orderBy)) {
                $OrderByCond = ' ORDER BY ' . implode(',', $orderBy);
            } else {
                $OrderByCond = ' ORDER BY ' . $orderBy;
            }
        } else {
            $OrderByCond = '';
        }
        if (is_array($columnsList)) {
            return $this->msDbLink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $this->msTable . " " . $Where . ' ' . $OrderByCond, $indexBy);
        } else {
            return $this->msDbLink->queryToArray('SELECT ' . $columnsList . ' FROM ' . $this->msTable . " " . $Where . ' ' . $OrderByCond, $indexBy);
        }
    }

    /**
     * Vrací z databáze sloupečky podle podmínek
     *
     * @param array        $columnsList seznam položek
     * @param array|int    $conditions  pole podmínek nebo ID záznamu
     * @param array|string $orderBy     třídit dle
     * @param string       $indexBy     klice vysledku naplnit hodnotou ze
     *                                  sloupečku
     * @param int $limit maximální počet vrácených záznamů
     *
     * @return array
     */
    public function getColumnsFromMySQL($columnsList, $conditions = null, $orderBy = null, $indexBy = null, $limit = null)
    {
        if (($columnsList != '*') && !count($columnsList)) {
            $this->error('getColumnsFromMySQL: Missing ColumnList');

            return null;
        }

        if (is_int($conditions)) {
            $conditions = array($this->getmyKeyColumn() => $conditions);
        }

        if (!count($conditions) && $this->getMyKey()) {
            $conditions[$this->myKeyColumn] = $this->getMyKey();
        }

        $where = '';
        if (is_array($conditions)) {
            if (!count($conditions)) {
                $this->error('getColumnsFromMySQL: Missing Conditions');

                return null;
            }
            $where = ' WHERE ' . $this->myDbLink->prepSelect($conditions);
        } else {
            if (!is_null($conditions)) {
                $where = ' WHERE ' . $conditions;
            }
        }

        if (is_array($indexBy)) {
            $indexBy = implode(',', $indexBy);
        }

        if ($orderBy) {
            if (is_array($orderBy)) {
                $orderByCond = ' ORDER BY ' . implode(',', $orderBy);
            } else {
                $orderByCond = ' ORDER BY ' . $orderBy;
            }
        } else {
            $orderByCond = '';
        }

        if ($limit) {
            $LimitCond = ' LIMIT ' . $limit;
        } else {
            $LimitCond = '';
        }

        if (is_array($columnsList)) {
            return $this->myDbLink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $this->myTable . ' ' . $where . $orderByCond . $LimitCond, $indexBy);
        } else {
            return $this->myDbLink->queryToArray('SELECT ' . $columnsList . ' FROM ' . $this->myTable . ' ' . $where . $orderByCond . $LimitCond, $indexBy);
        }
    }

    /**
     * Nacte data z pohody. Pokud nejsou zadány podmínky pokusi se načíst
     * položku s ID MSKeyColumn
     *
     * @param array $conditions      Pole dosazena za podminky
     * @param bool  $allowMultiplete nevarovat při vícenásobném výsledku
     *
     * @return array Načtená data nebo null
     */
    public function loadFromMSSQL($conditions = null, $allowMultiplete = false)
    {
        if (is_integer($conditions)) {
            $dataRow = $this->msDbLink->queryToArray("SELECT * FROM " . $this->msTable . " WHERE " . $this->msDbLink->prepSelect(array($this->MSKeyColumn => $conditions)));
        } else {
            if (is_array($conditions)) {
                $dataRow = $this->msDbLink->queryToArray("SELECT * FROM " . $this->msTable . " WHERE " . $this->msDbLink->prepSelect($conditions));
            } else {
                $dataRow = $this->msDbLink->queryToArray("SELECT * FROM " . $this->msTable . " WHERE " . $this->msDbLink->prepSelect(array($this->MSKeyColumn => $conditions)));
            }
        }

        if (!count($dataRow)) {
            return null;
        }
        if (count($dataRow) == 1) {
            $this->setData($dataRow[0], 'MSSQL');
        } else {
            if (!$allowMultiplete) {
                $this->addToLog('loadFromMSSQL: Multiplete result when single result expected: ' . $this->msDbLink->getLastQuery(), 'error');

                return null;
            }

            return $dataRow;
        }

        return count($this->getData('MSSQL'));
    }

    /**
     * Načte všechny záznamy z pohody
     *
     * @param string $tableName   jméno tabulky
     * @param array  $columnsList sloupečky k načtené
     * @param string $orderBy     sloupečky ke třídení
     *
     * @return array
     */
    public function getAllFromMSSQL($tableName = null, $columnsList = null, $orderBy = null)
    {
        if (!$tableName) {
            $tableName = $this->msTable;
        }
        if ($orderBy) {
            $orderByCond = ' ORDER BY ' . implode(',', $orderBy);
        } else {
            $orderByCond = '';
        }
        if (!$columnsList) {
            return $this->msDbLink->queryToArray("SELECT * FROM " . $tableName . $orderByCond);
        } else {
            return $this->msDbLink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $tableName . $orderByCond);
        }
    }

    /**
     * Načte z MySQL data k aktuálnímu $ItemID
     *
     * @param int $itemID klíč záznamu
     *
     * @return array Results
     */
    public function getDataFromMySQL($itemID = null)
    {
        if (is_null($itemID)) {
            $itemID = $this->getMyKey();
        }
        if (is_string($itemID)) {
            $itemID = '\'' . $this->easeAddSlashes($itemID) . '\'';
        } else {
            $itemID = $this->easeAddSlashes($itemID);
        }
        if (is_null($itemID)) {
            $this->error('loadFromMySQL: Unknown Key', $this->data);
        }

        $queryRaw = 'SELECT * FROM `' . $this->myTable . '` WHERE `' . $this->getmyKeyColumn() . '`=' . $itemID;

        return $this->myDbLink->queryToArray($queryRaw);
    }

    /**
     * Načte z MySQL data k aktuálnímu $ItemID a použije je v objektu
     *
     * @param int     $itemID     klíč záznamu
     * @param array   $dataPrefix název datové skupiny
     * @param boolean $multiplete nevarovat v případě více výsledků
     *
     * @return array Results
     */
    public function loadFromMySQL($itemID = null, $dataPrefix = null, $multiplete = false)
    {
        if (!$dataPrefix) {
            $dataPrefix = $this->defaultDataPrefix;
        }
        $mySQLResult = $this->getDataFromMySQL($itemID);
        if ($multiplete) {
            if ($dataPrefix) {
                $this->data[$dataPrefix] = $mySQLResult;
            } else {
                $this->data = $mySQLResult;
            }
        } else {
            if (count($mySQLResult) > 1) {
                $this->error('Multipete Query result: ' . $this->myDbLink->getLastQuery());
            }
            if (isset($mySQLResult[0])) {
                if ($dataPrefix) {
                    $this->data[$dataPrefix] = $mySQLResult[0];
                } else {
                    $this->data = $mySQLResult[0];
                }
            } else {
                return null;
            }
        }

        if (count($this->data)) {
            return count($this->data);
        } else {
            if (!$multiplete) {
                $this->addToLog('Item Found ' . $itemID . ' v ' . $this->myTable, 'error');
            }

            return null;
        }
    }

    /**
     * Vrátí z MySQL všechny záznamy
     *
     * @param string $tableName     jméno tabulky
     * @param array  $columnsList   získat pouze vyjmenované sloupečky
     * @param int    $limit         SQL Limit na vracene radky
     * @param string $orderByColumn jméno sloupečku pro třídění
     * @param string $ColumnToIndex jméno sloupečku pro indexaci
     *
     * @return array
     */
    public function getAllFromMySQL($tableName = null, $columnsList = null, $limit = null, $orderByColumn = null, $ColumnToIndex = null)
    {
        if (is_null($tableName)) {
            $tableName = $this->myTable;
        }
        if ($limit) {
            $limitCond = ' LIMIT ' . $limit;
        } else {
            $limitCond = '';
        }
        if ($orderByColumn) {
            if (is_array($orderByColumn)) {
                $orderByCond = ' ORDER BY ' . implode(',', $orderByColumn);
            } else {
                $orderByCond = ' ORDER BY ' . $orderByColumn;
            }
        } else {
            $orderByCond = '';
        }

        if (!$columnsList) {
            return $this->myDbLink->queryToArray("SELECT * FROM " . $tableName . $limitCond . $orderByCond, $ColumnToIndex);
        } else {
            return $this->myDbLink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $tableName . $limitCond . $orderByCond, $ColumnToIndex);
        }
    }

    /**
     * Inicializueje MSSQL
     *
     * @param $updateStructure znovunahraje strukturu
     */
    public function msSqlUp($updateStructure = false)
    {
        if (!is_object($this->msDbLink)) {
            try {
                require_once 'EaseMSSQL.php';
                $this->msDbLink = EaseDbMSSql::singleton($this->msSQLMode);
            } catch (Exception $exc) {
                $this->error($exc->getTraceAsString());
            }
            $this->msDbLink->setObjectName($this->getObjectName() . '->MSSQL');
        }

        $this->msDbLink->keyColumn = $this->MSKeyColumn;
        $this->msDbLink->tableName = $this->msTable;
        $this->msDbLink->LastModifiedColumn = $this->msLastModifiedColumn;
        $this->msDbLink->CreateColumn = $this->msCreateColumn;
        if ($updateStructure) {
            $this->loadSqlStruct('ms');
        }
        if (isset($this->sqlStruct['ms'])) {
            $this->msDbLink->tableStructure = $this->sqlStruct['ms'];
        }
    }

    /**
     * Oznámí MySQL objektu vlastnosti predvolene tabulky
     *
     * @param $updateStructure znovunahraje strukturu
     */
    public function mySqlUp($updateStructure = false)
    {
        if (!is_object($this->myDbLink)) {
            $this->takemyTable();

            return;
        }
        $this->myDbLink->keyColumn = $this->myKeyColumn;
        $this->myDbLink->tableName = $this->myTable;
        $this->myDbLink->CreateColumn = $this->myCreateColumn;
        $this->myDbLink->LastModifiedColumn = $this->myLastModifiedColumn;
        if ($updateStructure) {
            $this->loadSqlStruct('my');
        }
        if (isset($this->sqlStruct['my'])) {
            $this->myDbLink->tableStructure = $this->sqlStruct['my'];
        }
    }

    /**
     * Převezme data do $this->data a $this->data['MSSQL'] pokud se názvy políček shodují
     *
     * @param array $data
     *
     * @return array nezpracované položky
     */
    public function takeDataToMSSQL($data)
    {
        if (!count($this->sqlStruct['ms'])) {
            $this->loadObjectSqlStruct();
        }
        if (!count($this->sqlStruct['ms'])) {
            $this->error('takeData: Missing MSSQL struct for ' . $this->msTable, $data);
        }
        foreach ($this->sqlStruct['ms'] as $MSSQLColumnName => $MSSQLColumnValue) {
            if (array_key_exists($MSSQLColumnName, $data)) {
                $this->DivDataArray($data, $this->data['MSSQL'], $MSSQLColumnName);
                if (isset($this->data['MSSQL'][$MSSQLColumnName]) && ($MSSQLColumnValue['type'] == 'date')) {
                    $this->data['MSSQL'][$MSSQLColumnName] = EaseDbMSSql::ReformatDateFromMySQL($this->data['MSSQL'][$MSSQLColumnName]);
                }
            }
        }
    }

    /*
     * TODO - add work with table SQL struct - take only existing columns
      public function takeData($data, $dataType = 'MySQL')
      {
      $this->LoadObjectSqlStruct();
      foreach ($this->SqlStruct['my'] as $ShopColumnName => $ShopColumnValue)
      if (array_key_exists($ShopColumnName, $data)) {
      $this->DivDataArray($data, $this->data, $ShopColumnName);
      }
      if (count($data) && count(array_keys($data))) {
      $this->AddToLog('takeData: No info how to handle My: ' . implode(',', array_keys($data)) . ' on ' . $this->myTable, 'warning');
      }
      }
     *
     */

    /**
     * Vloží nový záznam do MSSQL tabulky
     *
     * @param array $data asiciativní pole dat
     *
     * @return int|null id nově vloženého řádku nebo null, pokud se data
     * nepovede vložit
     */
    public function insertToMSSQL($data = null)
    {
        if (!$data) {
            if (array_key_exists('MSSQL', $this->data)) {
                $data = $this->getData('MSSQL');
            } else {
                $data = $this->getData();
            }
        }
        if (!count($data)) {
            $this->error('NO data for Insert to MSSQL: ' . $this->msTable);

            return null;
        }

        unset($data[$this->MSKeyColumn]);
        if (isset($this->msCreateColumn) && strlen($this->msCreateColumn) && !isset($data[$this->msCreateColumn])) {
            $data[$this->msCreateColumn] = 'GetDate()';
        }
        list($cols, $vals) = $this->msDbLink->prepCols($data);
        $QueryRaw = 'INSERT INTO [' . MS_DB_DATABASE . '].[dbo].[' . $this->msTable . '] (' . $cols . ') VALUES (' . $vals . ')';
        if ($this->msDbLink->exeQuery($QueryRaw)) {
            $this->setMSKey($this->msDbLink->getlastInsertID());
            $this->Status['MSSQLSaved'] = true;

            return $this->getMSKey();
        }

        return null;
    }

    /**
     * Ulozi strukturu sql tabulek obektem defaultne pouzivanych
     *
     * @param string $sqlType ms|my|both
     * @param array  $columns sloupečky k vytvoření
     *
     * @return array vysledna struktura
     */
    public function saveSqlStruct($sqlType = 'both', $columns = null)
    {
        if ($sqlType == 'both') {
            if (!$columns) {
                $columns = $this->sqlStruct;
            }
            if (isset($columns['my']) && isset($columns['ms'])) {
                return $this->saveSqlStruct('my', $columns['my']) && $this->saveSqlStruct('ms', $columns['ms']);
            }
            if (isset($columns['my'])) {
                return $this->saveSqlStruct('my', $columns['my']);
            }
            if (isset($columns['ms'])) {
                return $this->saveSqlStruct('ms', $columns['ms']);
            }
        }
        $this->sqlStruct[$sqlType] = array();
        if (!is_array($columns)) {
            $this->error('SaveSqlStruct:  $Columns without key (' . $sqlType . ')', $columns);

            return null;
        } else {
            $TableName = key($columns);
        }
        $this->setObjectIdentity(array('myTable' => 'mysqlxmssql', 'myKeyColumn' => 'id', 'myCreateColumn' => false, 'myLastModifiedColumn' => false));
        $this->takemyTable();
        foreach ($columns[$TableName] as $columnName => $column) {
            if (is_string($column)) {
                $columnType = str_replace('*', '', $columnType);
            }
            $partner = null;
            switch ($sqlType) {
                case 'ms':
                    if ($columnName == $this->MSKeyColumn) {
                        $partner = $this->identity['myTable'] . '.' . $this->myRefIDColumn;
                    }
                    if ($columnName == $this->msIDSColumn) {
                        $partner = $this->identity['myTable'] . '.' . $this->myIDSColumn;
                    }
                    if ($columnName == $this->msRefIDColumn) {
                        $partner = $this->identity['myTable'] . '.' . $this->myKeyColumn;
                    }
                    break;
                case 'my':
                    if ($columnName == $this->myKeyColumn) {
                        $partner = $this->identity['MSTable'] . '.' . $this->msRefIDColumn;
                    }
                    if ($columnName == $this->myIDSColumn) {
                        $partner = $this->identity['MSTable'] . '.' . $this->msIDSColumn;
                    }
                    if ($columnName == $this->myRefIDColumn) {
                        $partner = $this->identity['MSTable'] . '.' . $this->MSKeyColumn;
                    }
                    break;
            }

            $columnType = $column['Type'];

            if (isset($column['Size'])) {
                $column .= '(' . $column['Size'] . ')';
            }

            $Record = array('sql' => $sqlType, 'table' => $TableName, 'column' => $columnName, 'type' => $columnType, 'partner' => $partner);
            $this->sqlStruct[$sqlType][$columnName] = $Record;

            $ShopID = $this->getMyKey(array('sql' => $sqlType, 'table' => $TableName, 'column' => $columnName));
            if (!$ShopID) {
                $this->myDbLink->arrayToInsert($Record);
            }
        }

        $this->restoreObjectIdentity();
        $this->takemyTable();

        return $this->sqlStruct[$sqlType];
    }

    /**
     * Uloží do struktury tabulek
     *
     * @param boolean $forceUpdate nepoužívá se
     */
    public function saveSqlStructArrays($forceUpdate = false)
    {
        $this->setObjectIdentity(
                array('myTable' => 'mysqlxmssql',
                    'myKeyColumn' => 'id',
                    'myCreateColumn' => false,
                    'myLastModifiedColumn' => false,
                    'MSTable' => false
                )
        );

        $this->takemyTable();
        foreach ($this->sqlStruct['my'] as $columnName => $structs) {
            $structs[$this->myKeyColumn] = $this->getMyKey(array('sql' => $structs['sql'], 'table' => $structs['table'], 'column' => $structs['column']));
            if ($structs[$this->myKeyColumn]) {
                $Result = $this->updateToMySQL($structs);
            } else {
                $Result = $this->insertToMySQL($structs);
            }
        }

        foreach ($this->sqlStruct['ms'] as $columnName => $structs) {
            $structs[$this->myKeyColumn] = $this->getMSKey(array('sql' => $structs['sql'], 'table' => $structs['table'], 'column' => $structs['column']));
            if ($structs[$this->myKeyColumn]) {
                $Result = $this->updateToMySQL($structs);
            } else {
                $Result = $this->insertToMySQL($structs);
            }
        }
        $this->restoreObjectIdentity();
        $this->takemyTable();
    }

    /**
     * Načte uloženou strukturu tabulky z databáze
     *
     * @param string $sqlType          'ms' nebo 'my'
     * @param string $tableName        jméno tabulky
     * @param string $tableNamePartner jméno tabulky na druhé straně, liší-li se
     *
     * @return array Sql Stuktura pro daný typ databáze
     */
    public function loadSqlStruct($sqlType, $tableName = null, $tableNamePartner = null)
    {
        $this->sqlStruct[$sqlType] = $this->msDbLink->describe($this->msTable);

        return $this->sqlStruct[$sqlType];

        if (!$tableName) {
            if ($sqlType == 'my') {
                $tableName = $this->myTable;
                $tableNamePartner = $this->msTable;
            } else {
                $tableName = $this->msTable;
                $tableNamePartner = $this->myTable;
            }
            if (!isset($tableNamePartner) || !$tableNamePartner) {
                $SqlTableStruct = $this->myDbLink->queryToArray('SELECT * FROM `mysqlxmssql` WHERE `sql` = \'' . $sqlType . '\' AND `table` LIKE \'' . $tableName . '\'', 'column');
            } else {
                $SqlTableStruct = $this->myDbLink->queryToArray('SELECT * FROM `mysqlxmssql` WHERE `sql` = \'' . $sqlType . '\' AND `table` LIKE \'' . $tableName . '\' AND `partner` LIKE \'' . $tableNamePartner . '.%\'', 'column');
            }
        } else {
            if (!$tableNamePartner) {
                $SqlTableStruct = $this->myDbLink->queryToArray('SELECT * FROM `mysqlxmssql` WHERE `sql` LIKE \'' . $sqlType . '\' AND `table` LIKE \'' . $tableName . '\'', 'column');
            } else {
                $SqlTableStruct = $this->myDbLink->queryToArray('SELECT * FROM `mysqlxmssql` WHERE `sql` LIKE \'' . $sqlType . '\' AND `table` LIKE \'' . $tableName . '\' AND `partner LIKE` \'' . $tableNamePartner . '.%\'', 'column');
            }
        }

        $this->sqlStruct[$sqlType] = $SqlTableStruct;
        if (count($this->sqlStruct[$sqlType])) {
            return $this->sqlStruct[$sqlType];
        } else {
            return null;
        }
    }

    /**
     * ulozi strukturu databazi objektu
     *
     * @param string $createOnly vytvoří strukturu pouze na jedné straně:  [my|ms]
     */
    public function saveObjectSqlStruct($createOnly = null)
    {
//        print_pre(array($this->myTable,$this->MSTable),'11');
        if ($this->msTable && ($createOnly != 'my')) {
            $this->saveSqlStruct('ms', $this->msDbLink->describe($this->msTable));
        }
        if ($this->myTable && ($createOnly != 'ms')) {
            $this->saveSqlStruct('my', $this->myDbLink->describe($this->myTable));
        }
//      print_pre(array($this->myTable,$this->MSTable),'22');
    }

    /**
     * Nacte strukturu databazovy tabulek do poli $this->SqlStruct()
     *
     * @return array
     */
    public function loadObjectSqlStruct()
    {
        if (isset($this->msTable) && strlen($this->msTable)) {

            if (!count($this->loadSqlStruct('ms'))) {
                if (is_object($this->msDbLink)) {
                    $this->saveSqlStruct('ms', $this->msDbLink->describe($this->msTable));
                } else {
                    $this->error('LoadObjectSqlStruct: Cant load MSSQL struct');
                }
            }
        }
        if (isset($this->myTable) && strlen($this->myTable)) {

            if (!count($this->loadSqlStruct('my'))) {
                if (is_object($this->myDbLink)) {
                    $this->saveSqlStruct('my', $this->myDbLink->describe($this->myTable));
                } else {
                    $this->error('LoadObjectSqlStruct: Cant load MySQL struct');
                }
            }
        }

        return $this->sqlStruct;
    }

    /**
     * Pokud jsou jsou známy protějšky stejného názvu automaticky je doplní do položky partner
     *
     * @param array $sqlStructProcessed pokud není určeno použije se $this->SqlStruct
     *
     * @return array $SqlStruct s vyplněnými položkami 'partner'
     */
    public function setupPartners($sqlStructProcessed = null)
    {
        if (!$sqlStructProcessed) {
            $sqlStructProcessed = $this->sqlStruct;
            $useInObject = true;
        } else {
            $useInObject = false;
        }
        $mySQLStruct = null;
        $msSSQLStruct = null;
        if (is_array($sqlStructProcessed['my'])) {
            if ($this->myTable == key($sqlStructProcessed['my'])) {
                $mySQLStruct = $sqlStructProcessed['my'][$this->myTable];
            } else {
                $mySQLStruct = $sqlStructProcessed['my'];
            }
            foreach ($mySQLStruct as $columnName => $structs) {
                if (isset($sqlStructProcessed['ms'][$columnName]) && is_array($sqlStructProcessed['ms'][$columnName])) {
                    $mySQLStruct[$columnName]['partner'] = $this->msTable . '.' . $columnName;
                    if (isset($sqlStructProcessed['ms'][$columnName]['keyid'])) {
                        $mySQLStruct[$columnName]['keyid'] = true;
                    }
                }
            }
        }

        if (is_array($sqlStructProcessed['ms'])) {
            if ($this->msTable == key($sqlStructProcessed['ms'])) {
                $msSSQLStruct = $sqlStructProcessed['ms'][$this->msTable];
            } else {
                $msSSQLStruct = $sqlStructProcessed['ms'];
            }
            foreach ($msSSQLStruct as $columnName => $structs) {
                if (isset($sqlStructProcessed['my'][$columnName]) && is_array($sqlStructProcessed['my'][$columnName])) {
                    $msSSQLStruct[$columnName]['partner'] = $this->myTable . '.' . $columnName;
                    if (isset($sqlStructProcessed['my'][$columnName]['keyid'])) {
                        $msSSQLStruct[$columnName]['keyid'] = true;
                    }
                }
            }
        }

        if ($useInObject) {
            $this->sqlStruct = array('my' => $mySQLStruct, 'ms' => $msSSQLStruct);

            return $this->sqlStruct;
        } else {
            return array('my' => $mySQLStruct, 'ms' => $msSSQLStruct);
        }
    }

    /**
     * Vrací funkce sloupečků v aktuální databázové tabulce
     *
     * @param string $sqlType
     *
     * @return array
     */
    public function getDbFunctions($sqlType)
    {
        $DbFunctions = array();
        if (!isset($this->sqlStruct[$sqlType])) {
            $this->loadSqlStruct($sqlType);
        }
        if (!isset($this->sqlStruct[$sqlType])) {
            return null;
        }

// tady jsem skončil .....
        foreach ($this->sqlStruct[$sqlType] as $columnName => $columnProperties) {
            if (isset($columnProperties['function'])) {
                $DbFunctions[$columnProperties['function']] = $columnName;
            }
        }

        return $DbFunctions;
    }

    /**
     * Naplní patřičné proměné názvy funkčních sloupečků
     */
    public function setUpColumnsRoles()
    {
        $this->msDbRoles = $this->getDbFunctions('ms');
        $this->myDbRoles = $this->getDbFunctions('my');
        if (isset($this->msDbRoles['KeyID'])) {
            $this->setMSKeyColumn($this->msDbRoles['KeyID']);
        }
        if (isset($this->myDbRoles['KeyID'])) {
            $this->setmyKeyColumn($this->myDbRoles['KeyID']);
        }
        if (isset($this->msDbRoles['IDS'])) {
            $this->msIDSColumn = $this->msDbRoles['IDS'];
        }
        if (isset($this->myDbRoles['IDS'])) {
            $this->myIDSColumn = $this->myDbRoles['IDS'];
        }
        if (isset($this->msDbRoles['RefKey'])) {
            $this->msRefIDColumn = $this->msDbRoles['RefKey'];
        }
        if (isset($this->myDbRoles['RefKey'])) {
            $this->myRefIDColumn = $this->myDbRoles['RefKey'];
        }
        if (isset($this->msDbRoles['LastModifiedDate'])) {
            $this->msLastModifiedColumn = $this->msDbRoles['LastModifiedDate'];
        }
        if (isset($this->myDbRoles['LastModifiedDate'])) {
            $this->myLastModifiedColumn = $this->myDbRoles['LastModifiedDate'];
        }
    }

    /**
     * Updatne záznam v pohodě
     *
     * @param array $data
     *
     * @return int Id záznamu nebo null v případě chyby
     */
    public function updateToMSSQL($data = null)
    {
        if (!$this->msTable) {
            $this->error('UpdateToMSSQL: No MSTable', $data);

            return null;
        }

        if (!$data) {
            if (array_key_exists('MSSQL', $this->data)) {
                $data = $this->getData('MSSQL');
            } else {
                $data = $this->getData();
            }
        }
        if (!count($data)) {
            $this->error('UpdateToMSSQL: Missing data');

            return null;
        }

        if (!isset($data[$this->MSKeyColumn]) || !$data[$this->MSKeyColumn]) {
            $this->error('UpdateToMSSQL: Missing MSKeyColumn', $data);

            return null;
        }

        $msKeyColumnBackup = $data[$this->MSKeyColumn];

        if (isset($this->msLastModifiedColumn)) {
            $data[$this->msLastModifiedColumn] = 'GetDate()';
        }
        if (isset($this->msCreateColumn)) {
            unset($data[$this->msCreateColumn]);
        }
        $msKeyColumnBackup = $data[$this->MSKeyColumn];
        unset($data[$this->MSKeyColumn]);

        $QueryRaw = '
UpDaTE [' . MS_DB_DATABASE . '].[dbo].[' . $this->msTable . '] SET ' . $this->msDbLink->prepUpdate($data, true) . '
WHERE [' . $this->MSKeyColumn . '] = ' . $msKeyColumnBackup;

        if ($this->msDbLink->exeQuery($QueryRaw)) {
            return $msKeyColumnBackup;
        }

        return null;
    }

    /**
     * Uloží pole dat do MSSQL.
     * Pokud je $SearchForID 0 updatuje pokud je nastaven  MSKeyColumn
     *
     * @param array $data        asociativní pole dat
     * @param bool  $searchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToMSSQL($data = null, $searchForID = false)
    {
        if (!$this->msTable) {
            $this->error('SaveToMSSQL: No MSTable', $data);

            return null;
        }
        if (!$data) {
            $data = $this->getData();
        }
        if (count($data) < 2) {
            $this->error('SaveToMSSQL: Missing data', $data);

            return null;
        }

        if ($searchForID) {
            if ($this->getMSSQLID(array($this->MSKeyColumn => $data[$this->MSKeyColumn]))) {
                $Result = $this->updateToMSSQL($data);
            } else {
                $Result = $this->insertToMSSQL($data);
            }
        } else {
            if (isset($data[$this->MSKeyColumn]) && $data[$this->MSKeyColumn]) {
                $Result = $this->updateToMSSQL($data);
            } else {
                $Result = $this->insertToMSSQL($data);
            }
        }

        if ($Result) {
            $this->Status['MSSQLSaved'] = true;

            return $this->data['MSSQL'][$this->MSKeyColumn];
        }

        return null;
    }

    /**
     * Proved update záznamu do MySQL
     *
     * @param array $data
     *
     * @return int Id záznamu nebo null v případě chyby
     */
    public function updateToMySQL($data = null)
    {
        if (!$this->myTable) {
            return null;
        }

        if (is_null($data)) {
            $defDatPref = $this->defaultDataPrefix;
            if (array_key_exists($defDatPref, $this->data)) {
                $data = $this->getData($defDatPref);
            } else {
                $data = $this->getData();
            }
            $useInObject = true;
        } else {
            $useInObject = false;
        }

        if (!count($data)) {
            $this->error(_('UpdateToMySQL: Chybějící data'));

            return null;
        }

        if (!isset($data[$this->myKeyColumn])) {
            $key = $this->getMyKey();
            if (is_null($key)) {
                $this->error('UpdateToMySQL: Unknown myKeyColumn:' . $this->myKeyColumn, $data);

                return null;
            }
        } else {
            $key = $data[$this->myKeyColumn];
            unset($data[$this->myKeyColumn]);
        }

        if (isset($this->myLastModifiedColumn) && !isset($data[$this->myLastModifiedColumn])) {
            $data[$this->myLastModifiedColumn] = 'NOW()';
        }

        if (!is_numeric($key)) {
            $key = '\'' . addslashes($key) . '\'';
        }

        $queryRaw = "UPDATE `" . $this->myTable . "` SET " . $this->myDbLink->arrayToQuery($data) . "  WHERE `" . $this->myKeyColumn . "` = " . $key;
        if ($this->myDbLink->exeQuery($queryRaw)) {
            if ($useInObject) {
                if (array_key_exists($defDatPref, $this->data)) {
                    return $this->data[$defDatPref][$this->myKeyColumn];
                } else {
                    return $this->data[$this->myKeyColumn];
                }
            } else {
                return $key;
            }
        }

        return null;
    }

    /**
     * Uloží pole dat do MySQL. Pokud je $SearchForID 0 updatuje pokud ze nastaven  myKeyColumn
     *
     * @param array $data        asociativní pole dat
     * @param bool  $searchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToMySQL($data = null, $searchForID = false)
    {
        if (!$this->myTable) {
            return null;
        }
        if (is_null($data)) {
            if (array_key_exists('MySQL', $this->data)) {
                $data = $this->getData('MySQL');
            } else {
                $data = $this->getData();
            }
        }

        if (count($data) < 1) {
            $this->error('SaveToMySQL: Missing data', $data);

            return null;
        }

        if ($searchForID) {
            if ($this->getMyKey($data)) {
                $rowsFound = $this->getColumnsFromMySQL($this->getmyKeyColumn(), array($this->getmyKeyColumn() => $this->getMyKey($data)));
            } else {
                $rowsFound = $this->getColumnsFromMySQL($this->getmyKeyColumn(), $data);
                if (count($rowsFound)) {
                    if (is_numeric($rowsFound[0][$this->getmyKeyColumn()])) {
                        $data[$this->getmyKeyColumn()] = (int) $rowsFound[0][$this->getmyKeyColumn()];
                    } else {
                        $data[$this->getmyKeyColumn()] = $rowsFound[0][$this->getmyKeyColumn()];
                    }
                }
            }

            if (count($rowsFound)) {
                $result = $this->updateToMySQL($data);
            } else {
                $result = $this->insertToMySQL($data);
            }
        } else {
            if (isset($data[$this->myKeyColumn]) && !is_null($data[$this->myKeyColumn]) && strlen($data[$this->myKeyColumn])) {
                $result = $this->updateToMySQL($data);
            } else {
                $result = $this->insertToMySQL($data);
            }
        }

        if (!is_null($result)) {
            $this->setMyKey($result);

            return $result;
        }

        return null;
    }

    /**
     * Vloží záznam do MySQL databáze
     *
     * @param array $data
     *
     * @return id
     */
    public function insertToMySQL($data = null)
    {
        if (is_null($data)) {
            if (array_key_exists('MySQL', $this->data)) {
                $data = $this->getData('MySQL');
            } else {
                $data = $this->getData();
            }
            $useInObject = true;
        } else {
            $useInObject = false;
        }

        if (!count($data)) {
            $this->error('NO data for Insert to MySQL: ' . $this->myTable);

            return null;
        }

        if ($this->myCreateColumn && !isset($data[$this->myCreateColumn])) {
            $data[$this->myCreateColumn] = 'NOW()';
        }
        $queryRaw = 'INSERT INTO `' . $this->myTable . '` SET ' . $this->myDbLink->arrayToQuery($data, false);
        if ($this->myDbLink->exeQuery($queryRaw)) {
            if ($useInObject) {
                $this->setMyKey($this->myDbLink->lastInsertID);
            }

            return $this->myDbLink->lastInsertID;
        }

        return null;
    }

    /**
     * Ulozi data objektu
     *
     * @return array ID zaznamu vlozenych nebo ulozenych
     */
    public function save()
    {
        $Result = array();
        if (is_object($this->myDbLink)) {
            $Result['my'] = $this->saveToMySQL();
        }
        if (is_object($this->msDbLink)) {
            $Result['ms'] = $this->saveToMSSQL();
        }

        return $Result;
    }

    /**
     * Vloží data do databází
     *
     * @param array $data
     */
    public function insert($data = null)
    {
        $this->InsertMode = '';

        $initialMySQLID = $this->getMySQLID();
        if ($initialMySQLID) {
            $this->InsertMode = 'SUpdate';
        } else {
            $this->InsertMode = 'SInsert';
        }
        $initialMSSQLID = $this->getMSSQLID();
        if ($initialMSSQLID) {
            $this->InsertMode .= 'PUpdate';
        } else {
            $this->InsertMode .= 'PInsert';
        }

        $this->data['MySQL'][$this->myKeyColumn] = $initialMySQLID;
        $this->data['MSSQL'][$this->MSKeyColumn] = $initialMSSQLID;

        switch ($this->InsertMode) {

            case "SUpdatePUpdate":
//                $this->loadFromMySQL($InitialShopID);
                $this->updateToMySQL();
//                $this->loadFromMSSQL($InitialMSSQLID);
                $this->updateToMSSQL();
                break;
            case "SUpdatePInsert":
                $CSVShopData = $this->data['MySQL'];
                $this->loadFromMySQL($initialMySQLID, 'MySQL');
                $this->data['MySQL'] = array_merge($this->data['MySQL'], $CSVShopData);
//                $this->TakeShopData();
                $this->takeMySQLData(null, true, false);

                $this->updateToShop();
                $this->insertToMSSQL();
                $this->setReferences();
                $this->updateToMySQL();
                $this->updateToMSSQL();
                break;
            case "SInsertPUpdate":
                $CSVMSSQLData = $this->data['MSSQL'];
                $this->loadFromMSSQL($initialMSSQLID);
                $this->data['MSSQL'] = array_merge($this->data['MSSQL'], $CSVMSSQLData);

                $this->setTagIDS();
                $this->takeMSSQLData(null, true);

                $this->updateToMSSQL();
                $this->insertToMySQL();
                $this->setReferences();
                $this->updateToMySQL();
                $this->updateToMSSQL();
                break;
            case "SInsertPInsert":
                $this->insertToMySQL();
                $this->insertToMSSQL();
                $this->setTagIDS();
                $this->setReferences();
                $this->updateToMySQL();
                $this->updateToMSSQL();
                break;
        }

        $SyncStatus = $this->isSynchronized();
        if (!$SyncStatus) {
            $this->error(
                    'Mega error nesynchronizovane produkty :' .
                    $this->InsertMode . ' MSSQL #' . $initialMSSQLID .
                    ' Shop: #' . $initialMySQLID
            );
        } else
            $this->addToLog(
                    'SyncOK: ' . $this->InsertMode .
                    ' MSSQL #' . $this->data['MSSQL'][$this->MSKeyColumn] .
                    ' Shop: #' . $this->data['MySQL'][$this->myKeyColumn]
            );

        $this->InsertMode = '';

        return $SyncStatus;
    }

    /**
     * pokud jsou znamy referencni sloupce, naplni se
     */
    public function setReferences()
    {
        if ($this->msRefIDColumn) {
            $shopColumnsOld = $this->data;
            if (isset($this->data['MSSQL'][$this->MSKeyColumn])) {
                $this->data[$this->myRefIDColumn] = $this->data['MSSQL'][$this->MSKeyColumn]; // (ID)
            }
            if (count(array_diff($this->data, $shopColumnsOld))) {
//                $this->Status['ShopSaved'] = false;
            }
        }
        if ($this->myRefIDColumn) {
            $msSQLColumnsOld = $this->data['MSSQL'];

            $this->data['MSSQL'][$this->msRefIDColumn] = $this->data[$this->myKeyColumn]; // (id)

            if (count(array_diff($this->data['MSSQL'], $msSQLColumnsOld))) {
//                $this->Status['MSSQLSaved'] = false;
            }
        }
    }

    /**
     * Vrací true pokud jsou MSSQL a shop synchronizovány
     *
     * @todo používá se to ještě ?
     *
     * @return bool
     */
    public function isSynchronized()
    {
        if (!isset($this->data['MSSQL'][$this->msIDSColumn]) ||
                !strlen($this->data['MSSQL'][$this->msIDSColumn])
        ) {
            $this->loadFromMSSQL($this->getMSKey());
        }
        if (!isset($this->data[$this->myIDSColumn]) ||
                !strlen($this->data[$this->myIDSColumn])
        ) {
            $this->loadFromMySQL($this->getMyKey());
        }
        if (!$this->data['MSSQL'][$this->msIDSColumn] || !$this->data[$this->myIDSColumn]) {
            return false;
        }

        return ($this->data['MSSQL'][$this->msIDSColumn] == $this->data[$this->myIDSColumn]);
    }

    /**
     * Nastaví hodnotu identifikačního sloupečku IDS
     *
     * @todo používá se to ještě ?
     *
     * @param bool $save Uložit záznam okamžitě ?
     *
     * @return string Hodnota  IDS
     */
    public function setTagIDS($save = false)
    {
        if ($this->data['MSSQL'][$this->msIDSColumn]) {
            $this->addToLog('Pokus o znovugenerovani jiz znameho IDS v pohode: ' . $this->data['MSSQL'][$this->msIDSColumn], 'warning');

            return $this->data['MSSQL'][$this->msIDSColumn];
        }

        $NumRowObject = new EaseNumRow($this->NumRowIDS, null, $this->RefAg);
        $IDS = $NumRowObject->NextValue(true);

        $this->data['MSSQL'][$this->msIDSColumn] = $IDS;

        $this->data[$this->myIDSColumn] = $IDS;
        //$this->Status = array('ShopSaved' => false, 'MSSQLSaved' => false);

        $this->addToLog('Generuji IDS: ' . $this->data['MSSQL'][$this->msIDSColumn], 'debug');

        if ($save)
            $this->updateToShop(array($this->myIDSColumn => $this->data[$this->myIDSColumn]));

        return $this->data['MSSQL'][$this->myIDSColumn];
    }

    /**
     * Smaže záznam z MySQL
     *
     * @param array|int $data
     *
     * @return bool
     */
    public function deleteFromMySQL($data = null)
    {
        if (is_int($data)) {
            $data = array($this->getmyKeyColumn() => intval($data));
        } else {
            if (is_null($data)) {
                $data = $this->getData();
            }
        }

        if (count($data)) {
            $this->myDbLink->exeQuery('DELETE FROM `' . $this->myTable . '` WHERE ' . $this->myDbLink->prepSelect($data));
            if ($this->myDbLink->getNumRows()) {
                return true;
            } else {
                return false;
            }
        } else {
            $this->error('DeleteFromMySQL: Unknown key.', $data);

            return false;
        }
    }

    /**
     * Přiřadí data z políčka do pole dat
     *
     * @param array  $data      asociativní pole dat
     * @param string $column    název položky k převzetí
     * @param bool   $mayBeNull nahrazovat chybejici hodnotu nullem ?
     * @param string $RenameAs  název cílového políčka
     *
     * @return mixed převzatá do pole
     */
    public function takeToData($data, $column, $mayBeNull = false, $RenameAs = null)
    {
        if (isset($data[$column])) {
            if ($RenameAs) {
                $this->setDataValue($RenameAs, $data[$column]);
            } else {
                $this->setDataValue($column, $data[$column]);
            }

            return $data[$column];
        } else {
            if ($mayBeNull) {
                $this->setDataValue($column, null);

                return null;
            }
        }
    }

    /**
     * Přiřadí data z políčka do pole pohody
     *
     * @param array  $data      asociativní pole dat
     * @param string $column    název položky k převzetí
     * @param bool   $mayBeNull nahrazovat chybejici hodnotu nullem ?
     * @param string $Reneme    název cílového políčka
     *
     * @todo pořešit práci s daty více databází
     *
     * @return mixed převzatá do pole
     */
    public function takeToMSSQL($data, $column, $mayBeNull = false, $renameAs = null)
    {
        if ($data[$column]) {
            if ($renameAs) {
                $this->data['MSSQL'][$renameAs] = $data[$column];
            } else {
                $this->data['MSSQL'][$column] = $data[$column];
            }

            return $data['MSSQL'][$column];
        } else {
            if ($mayBeNull) {
                $this->data['MSSQL'][$column] = null;

                return null;
            }
        }
    }

    /**
     * Vrátí IDčka záznamu tabulky v MSSQL
     *
     * @param string $tableName   jméno tabulky
     * @param string $msKeyColumn klíčový sloupeček
     *
     * @return array Pole hodnot klíčového sloupečku
     */
    public function getMSSQLList($tableName = null, $msKeyColumn = null)
    {
        if (!$tableName) {
            $tableName = $this->msTable;
        }
        if (!$msKeyColumn) {
            $msKeyColumn = $this->MSKeyColumn;
        }
        $ListQuery = "SELECT [$msKeyColumn] FROM [$tableName] ORDER BY [$msKeyColumn]";

        return $this->msDbLink->queryToArray($ListQuery);
    }

    /**
     * Načte IDčeka z tabulky
     *
     * @param string $tableName   jméno tabulky
     * @param string $myKeyColumn klíčovací sloupeček
     *
     * @return int počet položek
     */
    public function getMySQLList($tableName = null, $myKeyColumn = null)
    {
        if (!$tableName) {
            $tableName = $this->myTable;
        }
        if (!$myKeyColumn) {
            $myKeyColumn = $this->myKeyColumn;
        }
        $ListQuery = "SELECT `$myKeyColumn` FROM $tableName ";

        $this->myDbLink->queryToArray($ListQuery);
        $this->DataIdList = $this->myDbLink->resultArray;

        return count($this->DataIdList);
    }

    /**
     * Provede přiřazení MySQL tabulky objektu
     *
     * @param string $myTable
     */
    public function takemyTable($myTable = null)
    {
        if ($myTable) {
            $this->myTable = $myTable;
        }
        if (!isset($this->myDbLink) || !is_object($this->myDbLink)) {
            $this->myDbLink = EaseDB2MySql::singleton();
            if (!isset($this->easeShared->myDbLink)) {
                $this->easeShared->myDbLink = & $this->myDbLink;
            }
        }
        if (is_string($this->myTable)) {
            $this->mySqlUp();
        }
    }

    /**
     * Vrací název klíčového sloupce pro MySQL
     *
     * @return string
     */
    public function getmyKeyColumn()
    {
        return $this->myKeyColumn;
    }

    /**
     * Existuje záznam daného ID v databázi
     *
     * @param  int $id
     * @return int vrací počet položek s daným ID
     */
    public function MyIDExists($id)
    {
        return $this->myDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->myTable . ' WHERE ' . $this->getmyKeyColumn() . '=' . intval($id));
    }

    /**
     * Existuje záznam daného ID v databázi
     *
     * @param  int $id
     * @return int vrací počet položek s daným ID
     */
    public function MSIDExists($id)
    {
        return $this->msDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->msTable . ' WHERE ' . $this->getMSKeyColumn() . '=' . intval($id));
    }

    /**
     * Vrací název klíčového sloupce pro MSSQL
     *
     * @return string
     */
    public function getMSKeyColumn()
    {
        return $this->MSKeyColumn;
    }

    /**
     * Vrací hodnotu klíčového políčka pro MySQL
     *
     * @param array $data data z nichž se vrací hodnota klíče
     *
     * @return int
     */
    public function getMyKey($data = null)
    {
        if (!$data) {
            if (isset($this->data) && array_key_exists('MySQL', $this->data)) {
                $data = $this->getData('MySQL');
            } else {
                $data = $this->getData();
            }
        }
        if (isset($data) && isset($data[$this->myKeyColumn])) {
            return $data[$this->myKeyColumn];
        }

        return null;
    }

    /**
     * Nastavuje hodnotu klíčového políčka pro MySQL
     *
     * @param int|string $myKeyValue
     *
     * @return bool
     */
    public function setMyKey($myKeyValue)
    {
        if (isset($this->myKeyColumn)) {
            $this->setDataValue($this->myKeyColumn, $myKeyValue);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Vrací hodnotu klíčového políčka v Pohodě
     *
     * @param array $data data z nichž se vrací hodnota klíče
     *
     * @return int
     */
    public function getMSKey($data = null)
    {
        if (!$data) {
            $data = $this->getData('MSSQL');
        }
        if (isset($this->MSKeyColumn) && isset($data[$this->MSKeyColumn])) {
            return $data[$this->MSKeyColumn];
        } else {
            return null;
        }
    }

    /**
     * Nastavuje hodnotu klíčového políčka v Pohodě
     *
     * @param int|string $msKeyValue
     *
     * @return bool
     */
    public function setMSKey($msKeyValue)
    {
        if (isset($this->MSKeyColumn)) {
            $this->data['MSSQL'][$this->MSKeyColumn] = $msKeyValue;

            return true;
        } else {
            return false;
        }
    }

    /**
     * Nastaví jméno klíčového sloupečku v pohodě
     *
     * @param string $msKeyColumn
     */
    public function setMSKeyColumn($msKeyColumn)
    {
        $this->MSKeyColumn = $msKeyColumn;
    }

    /**
     * Nastaví jméno klíčového sloupečku v shopu
     *
     * @param string $myKeyColumn
     */
    public function setmyKeyColumn($myKeyColumn)
    {
        $this->myKeyColumn = $myKeyColumn;
    }

    /**
     * Nastaví aktuální pracovní tabulku pro MySQL
     *
     * @param string $myTable
     */
    public function setmyTable($myTable)
    {
        $this->myTable = $myTable;
        $this->setObjectIdentity(array('myTable' => $myTable));
        unset($this->sqlStruct['my']);
    }

    /**
     * Nastaví aktuální pracovní tabulku pro MSSQL
     *
     * @param string $msTable
     */
    public function setMSTable($msTable)
    {
        $this->msTable = $msTable;
        $this->setObjectIdentity(array('MSTable' => $msTable));
    }

    /**
     * Vrátí ID záznamu nalezeného v MSSQL podle pole $data
     *
     * @param array  $data     asociativní pole dat
     * @param string $operator SQL operator AND nebo OR
     *
     * @return unsigned int
     */
    public function getMSSQLID($data = null, $operator = 'AND')
    {
        if (!$data) {
            $data = $this->data['MSSQL'];
        }
        if (!count($data)) {
            $this->error('NENI PODLE CEHO URCIT JEDINECNOST PRODUKTU v shopu ', $this->data);

            return false;
        }

        if (count($data)) {
            foreach ($data as $ID => $value)
                if (!isset($value)) //vyhodit prazdne polozky
                    unset($data[$ID]);
        }

        $QueryRaw = "SELECT " . $this->MSKeyColumn . " FROM [" . $this->msTable . "] WHERE " . $this->msDbLink->prepSelect($data, $operator);
        $IDQuery = $this->msDbLink->queryToArray($QueryRaw);

        $this->LastMSSQLSearchResult = $IDQuery;

        return $IDQuery[0][$this->MSKeyColumn];
    }

    /**
     * Vrátí IDS záznamu nalezeného v pohodě podle pole $data
     *
     * @param array  $data     asociativní pole dat
     * @param string $operator SQL operator AND nebo OR
     *
     * @return unsigned int
     */
    public function getMSSQLIDS($data = null, $operator = 'AND')
    {
        if (!$data) {
            $data = $this->getData('MSSQL');
        }

        $ids = $this->getMSSQLValue($this->msIDSColumn);
        if ($ids) {
            return $ids;
        }

        if (!count($data)) {
            $this->error(_('NENI PODLE CEHO URCIT JEDINECNOST PRODUKTU v shopu ', $this->getData('MSSQL')));

            return false;
        }

        if (count($data)) {
            foreach ($data as $id => $value)
                if (!isset($value)) //vyhodit prazdne polozky
                    unset($data[$id]);
        }

        $queryRaw = "SELECT " . $this->msIDSColumn . " FROM [" . $this->msTable . "] WHERE " . $this->msDbLink->prepSelect($data, $operator);
        $idQuery = $this->msDbLink->queryToArray($queryRaw);
        if (isset($idQuery[0])) {
            return $idQuery[0][$this->msIDSColumn];
        } else {
            return null;
        }
    }

    /**
     * Smaže z pohody záznamy vyhovující podmínkám v poli $data a vrací počet smazaných záznamů
     *
     * @param array $data
     *
     * @return int
     */
    public function deleteFromMSSQL($data = null)
    {
        if (!$data)
            $data = $this->data['MSSQL'];

        if (!count($data)) {
            $this->error('NENI PODLE CEHO URCIT PRODUKTY k vymazani v Pohode ', $this->data['MSSQL']);

            return false;
        }

        if (count($data)) {
            foreach ($data as $id => $value)
                if (!isset($value)) //vyhodit prazdne polozky
                    unset($data[$id]);
        }

        $QueryRaw = "DELETE FROM [" . $this->msTable . "] WHERE " . $this->msDbLink->prepSelect($data);
        $this->msDbLink->exeQuery($QueryRaw);

        return $this->msDbLink->numRows;
    }

    /**
     * Vezme data z $this->data['MSSQL'] a prevede do $this->data
     *
     * @param bool $replace       přepisovat sloupečky mající již hodnotu ?
     * @param bool $takeKeyColumn klíčový sloupeček se defaultne ignoruje
     *
     * @return int počet provedených přiřazení
     */
    public function takeMSSQLData($replace = false, $takeKeyColumn = false)
    {
        $this->dataReset('MySQL');

        return $this->setData($this->getData('MSSQL'), 'MySQL');

        /*
          foreach ($this->SqlStruct['my'] as $ColName => $ColProperties) {
          if (!$ColProperties['partner']) //Brat v potaz pouze sloupecky se znamym partenerem
          continue;
          list($PartnerTable, $PartnerColumn) = explode('.', $ColProperties['partner']); //TODO: Zde brat v potaz moznost vice partneru oddelenych carkou
          if ($PartnerTable != $this->MSTable) //Preskocit vsechny protejsky z mimotabulek
          continue;
          if (isset($ColProperties['keyid']) && intval($ColProperties['keyid']) && !$TakeKeyColumn) //Preskocit klicove sloupecky je-li pozadovano
          continue;
          if (!$Replace && $this->getDataValue($ColName, 'MSSQL')) //Preskakovat neprazdne je-li pozadovano
          continue;
          $Success++;

          if (isset($ColProperties['type']['Type'])) {
          list($Type) = preg_split("/\(.*\)/", $ColProperties['type']['Type']);
          } else {
          list($Type) = preg_split("/\(.*\)/", $ColProperties['type']);
          }

          if (!isset($this->data['MSSQL'][$PartnerColumn])) {
          if ($this->Debug) {
          $this->addToLog('TakeMSSQLData: Partner MSSQL[' . $PartnerColumn . '] does not exists', 'waring');
          }
          //$this->data[$ColName] = null;
          $this->unsetDataValue($ColName, 'MySQL');
          continue;
          }

          switch ($Type) {
          case 'bit':
          case 'bool':
          case 'boolean':
          if ((strtolower($this->data['MSSQL'][$PartnerColumn]) == 'true') ||
          ( $this->data['MSSQL'][$PartnerColumn] == 1)
          ) {
          $this->setDataValue($ColName, true, 'MySQL');
          } else {
          $this->setDataValue($ColName, false, 'MySQL');
          }
          case 'tinyint':
          case 'smallint':
          case 'int':
          case 'bigint':
          $this->setDataValue($ColName, intval($this->getDataValue($PartnerColumn, 'MSSQL')), 'MySQL');
          break;
          case 'double':
          case 'decimal':
          case 'float':
          $this->setDataValue($ColName, floatval($this->getDataValue($PartnerColumn, 'MSSQL')), 'MySQL');
          break;
          case 'char':
          case 'varchar':
          case 'text':
          case 'datetime':
          case 'longtext':
          $this->setDataValue($ColName, $this->getDataValue($PartnerColumn, 'MSSQL'), 'MySQL');
          break;
          default:
          $this->addToLog('TakeMSSQLData: Unknown Column Type: "' . $Type . '"', 'waring');
          $this->setDataValue($ColName, $this->getDataValue($PartnerColumn, 'MSSQL'), 'MySQL');
          break;
          }
          } */

        return $success;
    }

    /**
     * Test na existenci tabulky v MySQL databázi
     *
     * @param string $tableName
     *
     * @return bool
     */
    public function mySQLTableExist($tableName = null)
    {
        if (!$tableName)
            $tableName = $this->myTable;
        if (!$tableName) {
            $this->error('ShopTableExist: $TableName not known', $this->identity);
        }

        return $this->myDbLink->tableExist($tableName);
    }

    /**
     * Test na existenci tabulky v MSSQL
     *
     * @param string $tableName
     *
     * @return bool
     */
    public function msSQLTableExist($tableName = null)
    {
        if (!$tableName){
            $tableName = $this->msTable;
        }
        if (!$tableName) {
            $this->error('MSSQLTableExist: $TableName not known', $this->identity);
        }

        return $this->msDbLink->tableExist($tableName);
    }

    /**
     * Vrátí počet položek tabulky v Pohodě
     *
     * @param string $tableName pokud není zadáno, použije se $this->MSTable
     *
     * @return int
     */
    public function getMSSQLItemsCount($tableName = null)
    {
        if (!$tableName) {
            $tableName = $this->msTable;
        }

        return $this->msDbLink->queryToValue('SELECT ROWS FROM sysindexes WHERE id = OBJECT_ID(\'' . $tableName . '\') AND indid = 1');
    }

    /**
     * Vrátí počet položek tabulky v MySQL
     *
     * @param string $tableName pokud není zadáno, použije se $this->myTable
     *
     * @return int
     */
    public function getMySQLItemsCount($tableName = null)
    {
        if (!$tableName) {
            $tableName = $this->myTable;
        }

        return $this->myDbLink->queryToValue('SELECT COUNT(' . $this->myKeyColumn . ') FROM ' . $tableName);
    }

    /**
     * Pouze malé a velké písmena
     * @return string text bez zvláštních znaků
     */
    public static function lettersOnly($text)
    {
        return preg_replace('/[^(a-zA-Z0-9)]*/','', $text);
    }
    
    /**
     * Prohledá zadané slupečky
     * 
     * @param string $searchTerm
     * @param array $columns
     */
    public function searchColumns($searchTerm,$columns){
        $sTerm = $this->myDbLink->AddSlashes($searchTerm);
        $conditons = array();
        foreach ($columns as $column){
            $conditons[] = '`'.$column.'` LIKE \'%'.$sTerm.'%\'';
        }
        return $this->myDbLink->queryToArray('SELECT * FROM '.$this->myTable.' WHERE '. implode(' OR ', $conditons) );
    }
    
}
