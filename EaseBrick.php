<?php

/**
 * Základní objekt pracující s databázemi
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */

require_once 'EaseSand.php';
require_once 'EaseMySQL.php';

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
    public $MyDbLink = null;

    /**
     * Objekt pro práci s MSSQL
     * @var EaseDbMSSql
     */
    public $MSDbLink = null;

    /**
     * Předvolená tabulka v MSSQL (součást identity)
     * @var string
     */
    public $MSTable = '';

    /**
     * Předvolená tabulka v MySQL (součást identity)
     * @var string
     */
    public $MyTable = '';

    /**
     * Sql Struktura databáze. Je obsažena ve dvou podpolích $SqlStruct['ms'] a $SqlStruct['my']
     * @var array
     */
    public $SqlStruct = null;

    /**
     * Počáteční způsob přístupu k MSSQL
     * @var string online||offline
     */
    public $MSSQLMode = 'online';

    /**
     * Funkční sloupečky pro MS
     * @var array
     */
    public $MSDbRoles = null;

    /**
     * Funkční sloupečky pro My
     * @var array
     */
    public $MyDbRoles = null;

    /**
     * Odkaz na objekt uživatele
     * @var EaseUser | EaseAnonym
     */
    public $User = null;

    /**
     * [Cs]Základní objekt pracující s databází
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->MSTable) {
            $this->msSqlUp();
        }

        if ($this->MyTable) {
            $this->takeMyTable($this->MyTable);
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
            $Key = $this->getMyKey($this->Data);
            if ($Key) {
                return parent::setObjectName(get_class($this) . '@' . $Key);
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
        if ($this->MyTable) {
            $this->mySqlUp();
        }
        if ($this->MSTable) {
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
                $targetObject->User = & $user;
            } else {
                $this->User = & $user;
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
        if (isset($this->User)) {
            $User = &$this->User;
        } else {
            if (isset($this->EaseShared->User)) {
                $User = &$this->EaseShared->User;
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
        if (is_object($this->MyDbLink) && is_resource($this->MyDbLink->SQLLink)) {
            return mysql_real_escape_string($text, $this->MyDbLink->SQLLink);
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
        if (!is_object($this->MSDbLink)) {
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
            $Where = ' WHERE ' . $this->MSDbLink->prepSelect($conditions);
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
            return $this->MSDbLink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $this->MSTable . " " . $Where . ' ' . $OrderByCond, $indexBy);
        } else {
            return $this->MSDbLink->queryToArray('SELECT ' . $columnsList . ' FROM ' . $this->MSTable . " " . $Where . ' ' . $OrderByCond, $indexBy);
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
            $conditions = array($this->getMyKeyColumn() => $conditions);
        }

        if (!count($conditions) && $this->getMyKey()) {
            $conditions[$this->MyKeyColumn] = $this->getMyKey();
        }

        $where = '';
        if (is_array($conditions)) {
            if (!count($conditions)) {
                $this->error('getColumnsFromMySQL: Missing Conditions');

                return null;
            }
            $where = ' WHERE ' . $this->MyDbLink->prepSelect($conditions);
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
            return $this->MyDbLink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $this->MyTable . ' ' . $where . $orderByCond . $LimitCond, $indexBy);
        } else {
            return $this->MyDbLink->queryToArray('SELECT ' . $columnsList . ' FROM ' . $this->MyTable . ' ' . $where . $orderByCond . $LimitCond, $indexBy);
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
            $dataRow = $this->MSDbLink->queryToArray("SELECT * FROM " . $this->MSTable . " WHERE " . $this->MSDbLink->prepSelect(array($this->MSKeyColumn => $conditions)));
        } else {
            if (is_array($conditions)) {
                $dataRow = $this->MSDbLink->queryToArray("SELECT * FROM " . $this->MSTable . " WHERE " . $this->MSDbLink->prepSelect($conditions));
            } else {
                $dataRow = $this->MSDbLink->queryToArray("SELECT * FROM " . $this->MSTable . " WHERE " . $this->MSDbLink->prepSelect(array($this->MSKeyColumn => $conditions)));
            }
        }

        if (!count($dataRow)) {
            return null;
        }
        if (count($dataRow) == 1) {
            $this->setData($dataRow[0], 'MSSQL');
        } else {
            if (!$allowMultiplete) {
                $this->addToLog('loadFromMSSQL: Multiplete result when single result expected: ' . $this->MSDbLink->getLastQuery(), 'error');

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
            $tableName = $this->MSTable;
        }
        if ($orderBy) {
            $orderByCond = ' ORDER BY ' . implode(',', $orderBy);
        } else {
            $orderByCond = '';
        }
        if (!$columnsList) {
            return $this->MSDbLink->queryToArray("SELECT * FROM " . $tableName . $orderByCond);
        } else {
            return $this->MSDbLink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $tableName . $orderByCond);
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
            $this->error('loadFromMySQL: Unknown Key', $this->Data);
        }

        $queryRaw = 'SELECT * FROM `' . $this->MyTable . '` WHERE `' . $this->getMyKeyColumn() . '`=' . $itemID;

        return $this->MyDbLink->queryToArray($queryRaw);
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
            $dataPrefix = $this->DefaultDataPrefix;
        }
        $MySQLResult = $this->getDataFromMySQL($itemID);
        if ($multiplete) {
            if ($dataPrefix) {
                $this->Data[$dataPrefix] = $MySQLResult;
            } else {
                $this->Data = $MySQLResult;
            }
        } else {
            if (count($MySQLResult) > 1) {
                $this->error('Multipete Query result: ' . $QueryRaw);
            }
            if (isset($MySQLResult[0])) {
                if ($dataPrefix) {
                    $this->Data[$dataPrefix] = $MySQLResult[0];
                } else {
                    $this->Data = $MySQLResult[0];
                }
            } else {
                return null;
            }
        }

        if (count($this->Data)) {
            return count($this->Data);
        } else {
            if (!$multiplete) {
                $this->addToLog('Item Found ' . $itemID . ' v ' . $this->MyTable, 'error');
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
            $tableName = $this->MyTable;
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
            return $this->MyDbLink->queryToArray("SELECT * FROM " . $tableName . $limitCond . $orderByCond, $ColumnToIndex);
        } else {
            return $this->MyDbLink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $tableName . $limitCond . $orderByCond, $ColumnToIndex);
        }
    }

    /**
     * Inicializueje MSSQL
     *
     * @param $updateStructure znovunahraje strukturu
     */
    public function msSqlUp($updateStructure = false)
    {
        if (!is_object($this->MSDbLink)) {
            try {
                require_once 'EaseMSSQL.php';
                $this->MSDbLink = EaseDbMSSql::singleton($this->MSSQLMode);
            } catch (Exception $exc) {
                $this->error($exc->getTraceAsString());
            }
            $this->MSDbLink->setObjectName($this->getObjectName() . '->MSSQL');
        }

        $this->MSDbLink->KeyColumn = $this->MSKeyColumn;
        $this->MSDbLink->TableName = $this->MSTable;
        $this->MSDbLink->LastModifiedColumn = $this->MSLastModifiedColumn;
        $this->MSDbLink->CreateColumn = $this->MSCreateColumn;
        if ($updateStructure) {
            $this->loadSqlStruct('ms');
        }
        if (isset($this->SqlStruct['ms'])) {
            $this->MSDbLink->TableStructure = $this->SqlStruct['ms'];
        }
    }

    /**
     * Oznámí MySQL objektu vlastnosti predvolene tabulky
     *
     * @param $updateStructure znovunahraje strukturu
     */
    public function mySqlUp($updateStructure = false)
    {
        if (!is_object($this->MyDbLink)) {
            $this->takeMyTable();

            return;
        }
        $this->MyDbLink->KeyColumn = $this->MyKeyColumn;
        $this->MyDbLink->TableName = $this->MyTable;
        $this->MyDbLink->CreateColumn = $this->MyCreateColumn;
        $this->MyDbLink->LastModifiedColumn = $this->MyLastModifiedColumn;
        if ($updateStructure) {
            $this->loadSqlStruct('my');
        }
        if (isset($this->SqlStruct['my'])) {
            $this->MyDbLink->TableStructure = $this->SqlStruct['my'];
        }
    }

    /**
     * Převezme data do $this->Data a $this->Data['MSSQL'] pokud se názvy políček shodují
     *
     * @param array $data
     *
     * @return array nezpracované položky
     */
    public function takeDataToMSSQL($data)
    {
        if (!count($this->SqlStruct['ms'])) {
            $this->loadObjectSqlStruct();
        }
        if (!count($this->SqlStruct['ms'])) {
            $this->error('takeData: Missing MSSQL struct for ' . $this->MSTable, $data);
        }
        foreach ($this->SqlStruct['ms'] as $MSSQLColumnName => $MSSQLColumnValue) {
            if (array_key_exists($MSSQLColumnName, $data)) {
                $this->DivDataArray($data, $this->Data['MSSQL'], $MSSQLColumnName);
                if (isset($this->Data['MSSQL'][$MSSQLColumnName]) && ($MSSQLColumnValue['type'] == 'date')) {
                    $this->Data['MSSQL'][$MSSQLColumnName] = EaseDbMSSql::ReformatDateFromMySQL($this->Data['MSSQL'][$MSSQLColumnName]);
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
      $this->DivDataArray($data, $this->Data, $ShopColumnName);
      }
      if (count($data) && count(array_keys($data))) {
      $this->AddToLog('takeData: No info how to handle My: ' . implode(',', array_keys($data)) . ' on ' . $this->MyTable, 'warning');
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
            if (array_key_exists('MSSQL', $this->Data)) {
                $data = $this->getData('MSSQL');
            } else {
                $data = $this->getData();
            }
        }
        if (!count($data)) {
            $this->error('NO data for Insert to MSSQL: ' . $this->MSTable);

            return null;
        }

        unset($data[$this->MSKeyColumn]);
        if (isset($this->MSCreateColumn) && strlen($this->MSCreateColumn) && !isset($data[$this->MSCreateColumn])) {
            $data[$this->MSCreateColumn] = 'GetDate()';
        }
        list($cols, $vals) = $this->MSDbLink->prepCols($data);
        $QueryRaw = 'INSERT INTO [' . MS_DB_DATABASE . '].[dbo].[' . $this->MSTable . '] (' . $cols . ') VALUES (' . $vals . ')';
        if ($this->MSDbLink->exeQuery($QueryRaw)) {
            $this->setMSKey($this->MSDbLink->getLastInsertID());
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
                $columns = $this->SqlStruct;
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
        $this->SqlStruct[$sqlType] = array();
        if (!is_array($columns)) {
            $this->error('SaveSqlStruct:  $Columns without key (' . $sqlType . ')', $columns);

            return null;
        } else {
            $TableName = key($columns);
        }
        $this->setObjectIdentity(array('MyTable' => 'mysqlxmssql', 'MyKeyColumn' => 'id', 'MyCreateColumn' => false, 'MyLastModifiedColumn' => false));
        $this->takeMyTable();
        foreach ($columns[$TableName] as $columnName => $column) {
            if (is_string($column)) {
                $columnType = str_replace('*', '', $columnType);
            }
            $partner = null;
            switch ($sqlType) {
                case 'ms':
                    if ($columnName == $this->MSKeyColumn) {
                        $partner = $this->Identity['MyTable'] . '.' . $this->MyRefIDColumn;
                    }
                    if ($columnName == $this->MSIDSColumn) {
                        $partner = $this->Identity['MyTable'] . '.' . $this->MyIDSColumn;
                    }
                    if ($columnName == $this->MSRefIDColumn) {
                        $partner = $this->Identity['MyTable'] . '.' . $this->MyKeyColumn;
                    }
                    break;
                case 'my':
                    if ($columnName == $this->MyKeyColumn) {
                        $partner = $this->Identity['MSTable'] . '.' . $this->MSRefIDColumn;
                    }
                    if ($columnName == $this->MyIDSColumn) {
                        $partner = $this->Identity['MSTable'] . '.' . $this->MSIDSColumn;
                    }
                    if ($columnName == $this->MyRefIDColumn) {
                        $partner = $this->Identity['MSTable'] . '.' . $this->MSKeyColumn;
                    }
                    break;
            }

            $columnType = $column['Type'];

            if (isset($column['Size'])) {
                $column .= '(' . $column['Size'] . ')';
            }

            $Record = array('sql' => $sqlType, 'table' => $TableName, 'column' => $columnName, 'type' => $columnType, 'partner' => $partner);
            $this->SqlStruct[$sqlType][$columnName] = $Record;

            $ShopID = $this->getMyKey(array('sql' => $sqlType, 'table' => $TableName, 'column' => $columnName));
            if (!$ShopID) {
                $this->MyDbLink->arrayToInsert($Record);
            }
        }

        $this->restoreObjectIdentity();
        $this->takeMyTable();

        return $this->SqlStruct[$sqlType];
    }

    /**
     * Uloží do struktury tabulek
     *
     * @param boolean $forceUpdate nepoužívá se
     */
    public function saveSqlStructArrays($forceUpdate = false)
    {
        $this->setObjectIdentity(
                array('MyTable' => 'mysqlxmssql',
                    'MyKeyColumn' => 'id',
                    'MyCreateColumn' => false,
                    'MyLastModifiedColumn' => false,
                    'MSTable' => false
                )
        );

        $this->takeMyTable();
        foreach ($this->SqlStruct['my'] as $columnName => $structs) {
            $structs[$this->MyKeyColumn] = $this->getMyKey(array('sql' => $structs['sql'], 'table' => $structs['table'], 'column' => $structs['column']));
            if ($structs[$this->MyKeyColumn]) {
                $Result = $this->updateToMySQL($structs);
            } else {
                $Result = $this->insertToMySQL($structs);
            }
        }

        foreach ($this->SqlStruct['ms'] as $columnName => $structs) {
            $structs[$this->MyKeyColumn] = $this->getMSKey(array('sql' => $structs['sql'], 'table' => $structs['table'], 'column' => $structs['column']));
            if ($structs[$this->MyKeyColumn]) {
                $Result = $this->updateToMySQL($structs);
            } else {
                $Result = $this->insertToMySQL($structs);
            }
        }
        $this->restoreObjectIdentity();
        $this->takeMyTable();
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
        $this->SqlStruct[$sqlType] = $this->MSDbLink->describe($this->MSTable);

        return $this->SqlStruct[$sqlType];

        if (!$tableName) {
            if ($sqlType == 'my') {
                $tableName = $this->MyTable;
                $tableNamePartner = $this->MSTable;
            } else {
                $tableName = $this->MSTable;
                $tableNamePartner = $this->MyTable;
            }
            if (!isset($tableNamePartner) || !$tableNamePartner) {
                $SqlTableStruct = $this->MyDbLink->queryToArray('SELECT * FROM `mysqlxmssql` WHERE `sql` = \'' . $sqlType . '\' AND `table` LIKE \'' . $tableName . '\'', 'column');
            } else {
                $SqlTableStruct = $this->MyDbLink->queryToArray('SELECT * FROM `mysqlxmssql` WHERE `sql` = \'' . $sqlType . '\' AND `table` LIKE \'' . $tableName . '\' AND `partner` LIKE \'' . $tableNamePartner . '.%\'', 'column');
            }
        } else {
            if (!$tableNamePartner) {
                $SqlTableStruct = $this->MyDbLink->queryToArray('SELECT * FROM `mysqlxmssql` WHERE `sql` LIKE \'' . $sqlType . '\' AND `table` LIKE \'' . $tableName . '\'', 'column');
            } else {
                $SqlTableStruct = $this->MyDbLink->queryToArray('SELECT * FROM `mysqlxmssql` WHERE `sql` LIKE \'' . $sqlType . '\' AND `table` LIKE \'' . $tableName . '\' AND `partner LIKE` \'' . $tableNamePartner . '.%\'', 'column');
            }
        }

        $this->SqlStruct[$sqlType] = $SqlTableStruct;
        if (count($this->SqlStruct[$sqlType])) {
            return $this->SqlStruct[$sqlType];
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
//        print_pre(array($this->MyTable,$this->MSTable),'11');
        if ($this->MSTable && ($createOnly != 'my')) {
            $this->saveSqlStruct('ms', $this->MSDbLink->describe($this->MSTable));
        }
        if ($this->MyTable && ($createOnly != 'ms')) {
            $this->saveSqlStruct('my', $this->MyDbLink->describe($this->MyTable));
        }
//      print_pre(array($this->MyTable,$this->MSTable),'22');
    }

    /**
     * Nacte strukturu databazovy tabulek do poli $this->SqlStruct()
     *
     * @return array
     */
    public function loadObjectSqlStruct()
    {
        if (isset($this->MSTable) && strlen($this->MSTable)) {

            if (!count($this->loadSqlStruct('ms'))) {
                if (is_object($this->MSDbLink)) {
                    $this->saveSqlStruct('ms', $this->MSDbLink->describe($this->MSTable));
                } else {
                    $this->error('LoadObjectSqlStruct: Cant load MSSQL struct');
                }
            }
        }
        if (isset($this->MyTable) && strlen($this->MyTable)) {

            if (!count($this->loadSqlStruct('my'))) {
                if (is_object($this->MyDbLink)) {
                    $this->saveSqlStruct('my', $this->MyDbLink->describe($this->MyTable));
                } else {
                    $this->error('LoadObjectSqlStruct: Cant load MySQL struct');
                }
            }
        }

        return $this->SqlStruct;
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
            $sqlStructProcessed = $this->SqlStruct;
            $useInObject = true;
        } else {
            $useInObject = false;
        }
        $mySQLStruct = null;
        $msSSQLStruct = null;
        if (is_array($sqlStructProcessed['my'])) {
            if ($this->MyTable == key($sqlStructProcessed['my'])) {
                $mySQLStruct = $sqlStructProcessed['my'][$this->MyTable];
            } else {
                $mySQLStruct = $sqlStructProcessed['my'];
            }
            foreach ($mySQLStruct as $columnName => $structs) {
                if (isset($sqlStructProcessed['ms'][$columnName]) && is_array($sqlStructProcessed['ms'][$columnName])) {
                    $mySQLStruct[$columnName]['partner'] = $this->MSTable . '.' . $columnName;
                    if (isset($sqlStructProcessed['ms'][$columnName]['keyid'])) {
                        $mySQLStruct[$columnName]['keyid'] = true;
                    }
                }
            }
        }

        if (is_array($sqlStructProcessed['ms'])) {
            if ($this->MSTable == key($sqlStructProcessed['ms'])) {
                $msSSQLStruct = $sqlStructProcessed['ms'][$this->MSTable];
            } else {
                $msSSQLStruct = $sqlStructProcessed['ms'];
            }
            foreach ($msSSQLStruct as $columnName => $structs) {
                if (isset($sqlStructProcessed['my'][$columnName]) && is_array($sqlStructProcessed['my'][$columnName])) {
                    $msSSQLStruct[$columnName]['partner'] = $this->MyTable . '.' . $columnName;
                    if (isset($sqlStructProcessed['my'][$columnName]['keyid'])) {
                        $msSSQLStruct[$columnName]['keyid'] = true;
                    }
                }
            }
        }

        if ($useInObject) {
            $this->SqlStruct = array('my' => $mySQLStruct, 'ms' => $msSSQLStruct);

            return $this->SqlStruct;
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
        if (!isset($this->SqlStruct[$sqlType])) {
            $this->loadSqlStruct($sqlType);
        }
        if (!isset($this->SqlStruct[$sqlType])) {
            return null;
        }

// tady jsem skončil .....
        foreach ($this->SqlStruct[$sqlType] as $columnName => $columnProperties) {
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
        $this->MSDbRoles = $this->getDbFunctions('ms');
        $this->MyDbRoles = $this->getDbFunctions('my');
        if (isset($this->MSDbRoles['KeyID'])) {
            $this->setMSKeyColumn($this->MSDbRoles['KeyID']);
        }
        if (isset($this->MyDbRoles['KeyID'])) {
            $this->setMyKeyColumn($this->MyDbRoles['KeyID']);
        }
        if (isset($this->MSDbRoles['IDS'])) {
            $this->MSIDSColumn = $this->MSDbRoles['IDS'];
        }
        if (isset($this->MyDbRoles['IDS'])) {
            $this->MyIDSColumn = $this->MyDbRoles['IDS'];
        }
        if (isset($this->MSDbRoles['RefKey'])) {
            $this->MSRefIDColumn = $this->MSDbRoles['RefKey'];
        }
        if (isset($this->MyDbRoles['RefKey'])) {
            $this->MyRefIDColumn = $this->MyDbRoles['RefKey'];
        }
        if (isset($this->MSDbRoles['LastModifiedDate'])) {
            $this->MSLastModifiedColumn = $this->MSDbRoles['LastModifiedDate'];
        }
        if (isset($this->MyDbRoles['LastModifiedDate'])) {
            $this->MyLastModifiedColumn = $this->MyDbRoles['LastModifiedDate'];
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
        if (!$this->MSTable) {
            $this->error('UpdateToMSSQL: No MSTable', $data);

            return null;
        }

        if (!$data) {
            if (array_key_exists('MSSQL', $this->Data)) {
                $data = $this->getData('MSSQL');
            } else {
                $data = $this->getData();
            }
        }
        if (!count($data)) {
            $this->error('UpdateToMSSQL: Missing Data');

            return null;
        }

        if (!isset($data[$this->MSKeyColumn]) || !$data[$this->MSKeyColumn]) {
            $this->error('UpdateToMSSQL: Missing MSKeyColumn', $data);

            return null;
        }

        $msKeyColumnBackup = $data[$this->MSKeyColumn];

        if (isset($this->MSLastModifiedColumn)) {
            $data[$this->MSLastModifiedColumn] = 'GetDate()';
        }
        if (isset($this->MSCreateColumn)) {
            unset($data[$this->MSCreateColumn]);
        }
        $msKeyColumnBackup = $data[$this->MSKeyColumn];
        unset($data[$this->MSKeyColumn]);

        $QueryRaw = '
UpDaTE [' . MS_DB_DATABASE . '].[dbo].[' . $this->MSTable . '] SET ' . $this->MSDbLink->prepUpdate($data, true) . '
WHERE [' . $this->MSKeyColumn . '] = ' . $msKeyColumnBackup;

        if ($this->MSDbLink->exeQuery($QueryRaw)) {
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
        if (!$this->MSTable) {
            $this->error('SaveToMSSQL: No MSTable', $data);

            return null;
        }
        if (!$data) {
            $data = $this->getData();
        }
        if (count($data) < 2) {
            $this->error('SaveToMSSQL: Missing Data', $data);

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

            return $this->Data['MSSQL'][$this->MSKeyColumn];
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
        if (!$this->MyTable) {
            return null;
        }

        if (is_null($data)) {
            $defDatPref = $this->DefaultDataPrefix;
            if (array_key_exists($defDatPref, $this->Data)) {
                $data = $this->getData($defDatPref);
            } else {
                $data = $this->getData();
            }
            $UseInObject = true;
        } else {
            $UseInObject = false;
        }

        if (!count($data)) {
            $this->error(_('UpdateToMySQL: Chybějící Data'));

            return null;
        }

        if (!isset($data[$this->MyKeyColumn])) {
            $key = $this->getMyKey();
            if (is_null($key)) {
                $this->error('UpdateToMySQL: Unknown MyKeyColumn:' . $this->MyKeyColumn, $data);

                return null;
            }
        } else {
            $key = $data[$this->MyKeyColumn];
            unset($data[$this->MyKeyColumn]);
        }

        if (isset($this->MyLastModifiedColumn) && !isset($data[$this->MyLastModifiedColumn])) {
            $data[$this->MyLastModifiedColumn] = 'NOW()';
        }

        if (!is_numeric($key)) {
            $key = '\'' . addslashes($key) . '\'';
        }

        $queryRaw = "UPDATE `" . $this->MyTable . "` SET " . $this->MyDbLink->arrayToQuery($data) . "  WHERE `" . $this->MyKeyColumn . "` = " . $key;
        if ($this->MyDbLink->exeQuery($queryRaw)) {
            if ($UseInObject) {
                if (array_key_exists($defDatPref, $this->Data)) {
                    return $this->Data[$defDatPref][$this->MyKeyColumn];
                } else {
                    return $this->Data[$this->MyKeyColumn];
                }
            } else {
                return $key;
            }
        }

        return null;
    }

    /**
     * Uloží pole dat do MySQL. Pokud je $SearchForID 0 updatuje pokud ze nastaven  MyKeyColumn
     *
     * @param array $data        asociativní pole dat
     * @param bool  $searchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToMySQL($data = null, $searchForID = false)
    {
        if (!$this->MyTable) {
            return null;
        }
        if (is_null($data)) {
            if (array_key_exists('MySQL', $this->Data)) {
                $data = $this->getData('MySQL');
            } else {
                $data = $this->getData();
            }
        }

        if (count($data) < 1) {
            $this->error('SaveToMySQL: Missing Data', $data);

            return null;
        }

        if ($searchForID) {
            if ($this->getMyKey($data)) {
                $rowsFound = $this->getColumnsFromMySQL($this->getMyKeyColumn(), array($this->getMyKeyColumn() => $this->getMyKey($data)));
            } else {
                $rowsFound = $this->getColumnsFromMySQL($this->getMyKeyColumn(), $data);
                if (count($rowsFound)) {
                    if (is_numeric($rowsFound[0][$this->getMyKeyColumn()])) {
                        $data[$this->getMyKeyColumn()] = (int) $rowsFound[0][$this->getMyKeyColumn()];
                    } else {
                        $data[$this->getMyKeyColumn()] = $rowsFound[0][$this->getMyKeyColumn()];
                    }
                }
            }

            if (count($rowsFound)) {
                $Result = $this->updateToMySQL($data);
            } else {
                $Result = $this->insertToMySQL($data);
            }
        } else {
            if (isset($data[$this->MyKeyColumn]) && !is_null($data[$this->MyKeyColumn]) && strlen($data[$this->MyKeyColumn])) {
                $Result = $this->updateToMySQL($data);
            } else {
                $Result = $this->insertToMySQL($data);
            }
        }

        if (!is_null($Result)) {
            $this->setMyKey($Result);

            return $Result;
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
            if (array_key_exists('MySQL', $this->Data)) {
                $data = $this->getData('MySQL');
            } else {
                $data = $this->getData();
            }
            $UseInObject = true;
        } else {
            $UseInObject = false;
        }

        if (!count($data)) {
            $this->error('NO data for Insert to Shop: ' . $this->MyTable);

            return null;
        }

        if ($this->MyCreateColumn && !isset($data[$this->MyCreateColumn])) {
            $data[$this->MyCreateColumn] = 'NOW()';
        }
        $QueryRaw = 'INSERT INTO `' . $this->MyTable . '` SET ' . $this->MyDbLink->arrayToQuery($data, false);
        if ($this->MyDbLink->exeQuery($QueryRaw)) {
            if ($UseInObject) {
                $this->setMyKey($this->MyDbLink->LastInsertID);
            }

            return $this->MyDbLink->LastInsertID;
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
        if (is_object($this->MyDbLink)) {
            $Result['my'] = $this->saveToMySQL();
        }
        if (is_object($this->MSDbLink)) {
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

        $this->Data['MySQL'][$this->MyKeyColumn] = $initialMySQLID;
        $this->Data['MSSQL'][$this->MSKeyColumn] = $initialMSSQLID;

        switch ($this->InsertMode) {

            case "SUpdatePUpdate":
//                $this->loadFromMySQL($InitialShopID);
                $this->updateToMySQL();
//                $this->loadFromMSSQL($InitialMSSQLID);
                $this->updateToMSSQL();
                break;
            case "SUpdatePInsert":
                $CSVShopData = $this->Data['MySQL'];
                $this->loadFromMySQL($initialMySQLID, 'MySQL');
                $this->Data['MySQL'] = array_merge($this->Data['MySQL'], $CSVShopData);
//                $this->TakeShopData();
                $this->takeMySQLData(null, true, false);

                $this->updateToShop();
                $this->insertToMSSQL();
                $this->setReferences();
                $this->updateToMySQL();
                $this->updateToMSSQL();
                break;
            case "SInsertPUpdate":
                $CSVMSSQLData = $this->Data['MSSQL'];
                $this->loadFromMSSQL($initialMSSQLID);
                $this->Data['MSSQL'] = array_merge($this->Data['MSSQL'], $CSVMSSQLData);

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
                    ' MSSQL #' . $this->Data['MSSQL'][$this->MSKeyColumn] .
                    ' Shop: #' . $this->Data['MySQL'][$this->MyKeyColumn]
            );

        $this->InsertMode = '';

        return $SyncStatus;
    }

    /**
     * pokud jsou znamy referencni sloupce, naplni se
     */
    public function setReferences()
    {
        if ($this->MSRefIDColumn) {
            $shopColumnsOld = $this->Data;
            if (isset($this->Data['MSSQL'][$this->MSKeyColumn])) {
                $this->Data[$this->MyRefIDColumn] = $this->Data['MSSQL'][$this->MSKeyColumn]; // (ID)
            }
            if (count(array_diff($this->Data, $shopColumnsOld))) {
//                $this->Status['ShopSaved'] = false;
            }
        }
        if ($this->MyRefIDColumn) {
            $msSQLColumnsOld = $this->Data['MSSQL'];

            $this->Data['MSSQL'][$this->MSRefIDColumn] = $this->Data[$this->MyKeyColumn]; // (id)

            if (count(array_diff($this->Data['MSSQL'], $msSQLColumnsOld))) {
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
        if (!isset($this->Data['MSSQL'][$this->MSIDSColumn]) ||
                !strlen($this->Data['MSSQL'][$this->MSIDSColumn])
        ) {
            $this->loadFromMSSQL($this->getMSKey());
        }
        if (!isset($this->Data[$this->MyIDSColumn]) ||
                !strlen($this->Data[$this->MyIDSColumn])
        ) {
            $this->loadFromMySQL($this->getMyKey());
        }
        if (!$this->Data['MSSQL'][$this->MSIDSColumn] || !$this->Data[$this->MyIDSColumn]) {
            return false;
        }

        return ($this->Data['MSSQL'][$this->MSIDSColumn] == $this->Data[$this->MyIDSColumn]);
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
        if ($this->Data['MSSQL'][$this->MSIDSColumn]) {
            $this->addToLog('Pokus o znovugenerovani jiz znameho IDS v pohode: ' . $this->Data['MSSQL'][$this->MSIDSColumn], 'warning');

            return $this->Data['MSSQL'][$this->MSIDSColumn];
        }

        $NumRowObject = new EaseNumRow($this->NumRowIDS, null, $this->RefAg);
        $IDS = $NumRowObject->NextValue(true);

        $this->Data['MSSQL'][$this->MSIDSColumn] = $IDS;

        $this->Data[$this->MyIDSColumn] = $IDS;
        //$this->Status = array('ShopSaved' => false, 'MSSQLSaved' => false);

        $this->addToLog('Generuji IDS: ' . $this->Data['MSSQL'][$this->MSIDSColumn], 'debug');

        if ($save)
            $this->updateToShop(array($this->MyIDSColumn => $this->Data[$this->MyIDSColumn]));

        return $this->Data['MSSQL'][$this->MyIDSColumn];
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
            $data = array($this->getMyKeyColumn() => intval($data));
        } else {
            if (is_null($data)) {
                $data = $this->getData();
            }
        }

        if (count($data)) {
            $this->MyDbLink->exeQuery('DELETE FROM `' . $this->MyTable . '` WHERE ' . $this->MyDbLink->prepSelect($data));
            if ($this->MyDbLink->getNumRows()) {
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
                $this->Data['MSSQL'][$renameAs] = $data[$column];
            } else {
                $this->Data['MSSQL'][$column] = $data[$column];
            }

            return $data['MSSQL'][$column];
        } else {
            if ($mayBeNull) {
                $this->Data['MSSQL'][$column] = null;

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
            $tableName = $this->MSTable;
        }
        if (!$msKeyColumn) {
            $msKeyColumn = $this->MSKeyColumn;
        }
        $ListQuery = "SELECT [$msKeyColumn] FROM [$tableName] ORDER BY [$msKeyColumn]";

        return $this->MSDbLink->queryToArray($ListQuery);
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
            $tableName = $this->MyTable;
        }
        if (!$myKeyColumn) {
            $myKeyColumn = $this->MyKeyColumn;
        }
        $ListQuery = "SELECT `$myKeyColumn` FROM $tableName ";

        $this->MyDbLink->queryToArray($ListQuery);
        $this->DataIdList = $this->MyDbLink->ResultArray;

        return count($this->DataIdList);
    }

    /**
     * Provede přiřazení MySQL tabulky objektu
     *
     * @param string $myTable
     */
    public function takeMyTable($myTable = null)
    {
        if ($myTable) {
            $this->MyTable = $myTable;
        }
        if (!isset($this->MyDbLink) || !is_object($this->MyDbLink)) {
            $this->MyDbLink = EaseDbMySqli::singleton();
            if (!isset($this->EaseShared->MyDblink)) {
                $this->EaseShared->MyDbLink = & $this->MyDbLink;
            }
        }
        if (is_string($this->MyTable)) {
            $this->mySqlUp();
        }
    }

    /**
     * Vrací název klíčového sloupce pro MySQL
     *
     * @return string
     */
    public function getMyKeyColumn()
    {
        return $this->MyKeyColumn;
    }

    /**
     * Existuje záznam daného ID v databázi
     *
     * @param  int $id
     * @return int vrací počet položek s daným ID
     */
    public function MyIDExists($id)
    {
        return $this->MyDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->MyTable . ' WHERE ' . $this->getMyKeyColumn() . '=' . intval($id));
    }

    /**
     * Existuje záznam daného ID v databázi
     *
     * @param  int $id
     * @return int vrací počet položek s daným ID
     */
    public function MSIDExists($id)
    {
        return $this->MSDbLink->queryToValue('SELECT COUNT(*) FROM ' . $this->MSTable . ' WHERE ' . $this->getMSKeyColumn() . '=' . intval($id));
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
            if (isset($this->Data) && array_key_exists('MySQL', $this->Data)) {
                $data = $this->getData('MySQL');
            } else {
                $data = $this->getData();
            }
        }
        if (isset($data) && isset($data[$this->MyKeyColumn])) {
            return $data[$this->MyKeyColumn];
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
        if (isset($this->MyKeyColumn)) {
            $this->setDataValue($this->MyKeyColumn, $myKeyValue);

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
            $this->Data['MSSQL'][$this->MSKeyColumn] = $msKeyValue;

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
    public function setMyKeyColumn($myKeyColumn)
    {
        $this->MyKeyColumn = $myKeyColumn;
    }

    /**
     * Nastaví aktuální pracovní tabulku pro MySQL
     *
     * @param string $myTable
     */
    public function setMyTable($myTable)
    {
        $this->MyTable = $myTable;
        $this->setObjectIdentity(array('MyTable' => $myTable));
        unset($this->SqlStruct['my']);
    }

    /**
     * Nastaví aktuální pracovní tabulku pro MSSQL
     *
     * @param string $msTable
     */
    public function setMSTable($msTable)
    {
        $this->MSTable = $msTable;
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
            $data = $this->Data['MSSQL'];
        }
        if (!count($data)) {
            $this->error('NENI PODLE CEHO URCIT JEDINECNOST PRODUKTU v shopu ', $this->Data);

            return false;
        }

        if (count($data)) {
            foreach ($data as $ID => $Value)
                if (!isset($Value)) //vyhodit prazdne polozky
                    unset($data[$ID]);
        }

        $QueryRaw = "SELECT " . $this->MSKeyColumn . " FROM [" . $this->MSTable . "] WHERE " . $this->MSDbLink->prepSelect($data, $operator);
        $IDQuery = $this->MSDbLink->queryToArray($QueryRaw);

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

        $ids = $this->getMSSQLValue($this->MSIDSColumn);
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

        $queryRaw = "SELECT " . $this->MSIDSColumn . " FROM [" . $this->MSTable . "] WHERE " . $this->MSDbLink->prepSelect($data, $operator);
        $idQuery = $this->MSDbLink->queryToArray($queryRaw);
        if (isset($idQuery[0])) {
            return $idQuery[0][$this->MSIDSColumn];
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
            $data = $this->Data['MSSQL'];

        if (!count($data)) {
            $this->error('NENI PODLE CEHO URCIT PRODUKTY k vymazani v Pohode ', $this->Data['MSSQL']);

            return false;
        }

        if (count($data)) {
            foreach ($data as $id => $value)
                if (!isset($value)) //vyhodit prazdne polozky
                    unset($data[$id]);
        }

        $QueryRaw = "DELETE FROM [" . $this->MSTable . "] WHERE " . $this->MSDbLink->prepSelect($data);
        $this->MSDbLink->exeQuery($QueryRaw);

        return $this->MSDbLink->NumRows;
    }

    /**
     * Vezme data z $this->Data['MSSQL'] a prevede do $this->Data
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

          if (!isset($this->Data['MSSQL'][$PartnerColumn])) {
          if ($this->Debug) {
          $this->addToLog('TakeMSSQLData: Partner MSSQL[' . $PartnerColumn . '] does not exists', 'waring');
          }
          //$this->Data[$ColName] = null;
          $this->unsetDataValue($ColName, 'MySQL');
          continue;
          }

          switch ($Type) {
          case 'bit':
          case 'bool':
          case 'boolean':
          if ((strtolower($this->Data['MSSQL'][$PartnerColumn]) == 'true') ||
          ( $this->Data['MSSQL'][$PartnerColumn] == 1)
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
            $tableName = $this->MyTable;
        if (!$tableName) {
            $this->error('ShopTableExist: $TableName not known', $this->Identity);
        }

        return $this->MyDbLink->tableExist($tableName);
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
        if (!$tableName)
            $tableName = $this->MSTable;
        if (!$tableName) {
            $this->error('MSSQLTableExist: $TableName not known', $this->Identity);
        }

        return $this->MSDbLink->tableExist($tableName);
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
            $tableName = $this->MSTable;
        }

        return $this->MSDbLink->queryToValue('SELECT ROWS FROM sysindexes WHERE id = OBJECT_ID(\'' . $tableName . '\') AND indid = 1');
    }

    /**
     * Vrátí počet položek tabulky v MySQL
     *
     * @param string $tableName pokud není zadáno, použije se $this->MyTable
     *
     * @return int
     */
    public function getMySQLItemsCount($tableName = null)
    {
        if (!$tableName) {
            $tableName = $this->MyTable;
        }

        return $this->MyDbLink->queryToValue('SELECT COUNT(' . $this->MyKeyColumn . ') FROM ' . $tableName);
    }

    /**
     * Pouze malé a velké písmena
     * @return string text bez zvláštních znaků
     */
    public static function lettersOnly($text)
    {
        return preg_replace('/[^(a-zA-Z0-9)]*/','', $text);
    }
}
