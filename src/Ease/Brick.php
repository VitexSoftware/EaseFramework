<?php

namespace Ease;

/**
 * Základní objekt pracující s databázemi.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
class Brick extends Sand
{
    /**
     * Objekt pro práci s SQL.
     *
     * @var SQL\PDO
     */
    public $dblink = null;

    /**
     * Předvolená tabulka v SQL (součást identity).
     *
     * @var string
     */
    public $myTable = '';

    /**
     * Odkaz na objekt uživatele.
     *
     * @var User | EaseAnonym
     */
    public $user = null;

    /**
     * Multiplete DATA indicator.
     *
     * @var boolean
     */
    protected $multipleteResult;

    /**
     * Basic class to implement ORM
     */
    public function __construct()
    {
        parent::__construct();

        if (!empty($this->myTable)) {
            $this->takemyTable($this->myTable);
        }

        $this->saveObjectIdentity();
    }

    /**
     * Nastavuje jméno objektu
     * Je li znnámý, doplní jméno objektu hodnotu klíče např EaseUser#vitex
     * nebo EaseProductInCart#4542.
     *
     * @param string $objectName
     *
     * @return string new name
     */
    public function setObjectName($objectName = null)
    {
        if (is_null($objectName)) {
            $key = $this->getMyKey($this->data);
            if ($key) {
                $result = parent::setObjectName(get_class($this).'@'.$key);
            } else {
                $result = parent::setObjectName();
            }
        } else {
            $result = parent::setObjectName($objectName);
        }

        return $result;
    }

    /**
     * Přidá zprávu do zásobníku pro zobrazení uživateli.
     *
     * @param string $message  zprava
     * @param string $type     Fronta zprav (warning|info|error|success)
     * @param bool   $addIcons prida UTF8 ikonky na zacatek zprav
     *
     * @return bool Was message added to message handler object ?
     */
    public function addStatusMessage($message, $type = 'info', $addIcons = true)
    {
        if ($addIcons) {
            $message = ' '.Logger\Message::getTypeUnicodeSymbol($type).' '.$message;
        }
        return parent::addStatusMessage($message, $type);
    }

    /**
     * Vrací z databáze sloupečky podle podmínek.
     *
     * @param array            $columnsList seznam položek
     * @param array|int|string $conditions  pole podmínek nebo ID záznamu
     * @param array|string     $orderBy     třídit dle
     * @param string           $indexBy     klice vysledku naplnit hodnotou ze
     *                                      sloupečku
     * @param int              $limit       maximální počet vrácených záznamů
     *
     * @return array
     */
    public function getColumnsFromSQL($columnsList, $conditions = null,
                                      $orderBy = null, $indexBy = null,
                                      $limit = null)
    {
        $cc = $this->dblink->getColumnComma();
        if (($columnsList != '*') && !count($columnsList)) {
            throw new Exception('getColumnsFromSQL: Missing ColumnList');
        }

        if (is_int($conditions)) {
            $conditions = [$this->getmyKeyColumn() => $conditions];
        }

        $where = '';
        if (is_array($conditions) && count($conditions)) {
            $where = SQL\SQL::$whr.$this->dblink->prepSelect($conditions);
        } else {
            if (!is_null($conditions)) {
                $where = SQL\SQL::$whr.$conditions;
            }
        }

        if (is_array($indexBy)) {
            $indexBy = implode(',', $indexBy);
        }

        if ($orderBy) {
            if (is_array($orderBy) && count($indexBy)) {
                foreach ($orderBy as $oid => $oname) {
                    $orderBy[$oid] = "`$oname`";
                }
                $orderByCond = SQL\SQL::$ord.implode(',', $orderBy);
            } else {
                $orderByCond = SQL\SQL::$ord.$orderBy;
            }
        } else {
            $orderByCond = '';
        }

        if (intval($limit)) {
            $limitCond = SQL\SQL::$lmt.$limit;
        } else {
            $limitCond = '';
        }

        if (is_array($columnsList)) {
            foreach ($columnsList as $id => $column) {
                $columnsList[$id] = $cc.$column.$cc;
            }

            return $this->dblink->queryToArray(SQL\SQL::$sel.implode(',',
                        $columnsList).SQL\SQL::$frm.$cc.$this->myTable.$cc.' '.$where.$orderByCond.$limitCond,
                    $indexBy);
        } else {
            if (!strstr($columnsList, '*')) {
                $columnsList = $cc.$columnsList.$cc;
            }

            return $this->dblink->queryToArray(SQL\SQL::$sel.$columnsList.' FROM '.$cc.$this->myTable.$cc.' '.$where.$orderByCond.$limitCond,
                    $indexBy);
        }
    }

    /**
     * Načte z SQL data k aktuálnímu $ItemID.
     *
     * @param int $itemID klíč záznamu
     *
     * @return array Results
     */
    public function getDataFromSQL($itemID = null)
    {
        if (is_null($itemID)) {
            $itemID = $this->getMyKey();
        }
        if (is_string($itemID)) {
            $itemID = "'".$this->dblink->addSlashes($itemID)."'";
        } else {
            $itemID = $this->dblink->addSlashes($itemID);
        } if (is_null($itemID)) {
            throw new Exception('loadFromSQL: Unknown Key');
        }
        $cc       = $this->dblink->getColumnComma();
        $queryRaw = SQL\SQL::$sel.' * FROM '.$cc.$this->myTable.$cc.SQL\SQL::$whr.$cc.$this->getmyKeyColumn().$cc.' = '.$itemID;

        return $this->dblink->queryToArray($queryRaw);
    }

    /**
     * Načte z SQL data k aktuálnímu $ItemID a použije je v objektu.
     *
     * @param int   $itemID     klíč záznamu
     *
     * @return array Results
     */
    public function loadFromSQL($itemID = null)
    {
        $rowsLoaded = null;
        if (is_null($itemID)) {
            $itemID = $this->getMyKey();
        }
        $sqlResult              = $this->getDataFromSQL($itemID);
        $this->multipleteResult = (count($sqlResult) > 1);

        if ($this->multipleteResult && is_array($sqlResult)) {
            $results = [];
            foreach ($sqlResult as $id => $data) {
                $this->takeData($data);
                $results[$id] = $this->getData();
            }
            $this->data = $results;
        } else {
            if (isset($sqlResult[0])) {
                $this->takeData($sqlResult[0]);
            }
        }
        if (count($this->data)) {
            $rowsLoaded = count($this->data);
        }

        return $rowsLoaded;
    }

    /**
     * Vrátí z SQL všechny záznamy.
     *
     * @param string $tableName     jméno tabulky
     * @param array  $columnsList   získat pouze vyjmenované sloupečky
     * @param int    $limit         SQL Limit na vracene radky
     * @param string $orderByColumn jméno sloupečku pro třídění
     * @param string $columnToIndex jméno sloupečku pro indexaci
     *
     * @return array
     */
    public function getAllFromSQL($tableName = null, $columnsList = null,
                                  $limit = null, $orderByColumn = null,
                                  $columnToIndex = null)
    {
        if (is_null($tableName)) {
            $tableName = $this->myTable;
        }

        if (is_null($limit)) {
            $limitCond = '';
        } else {
            $limitCond = SQL\SQL::$lmt.$limit;
        }
        if (is_null($orderByColumn)) {
            $orderByCond = '';
        } else {
            if (is_array($orderByColumn)) {
                $orderByCond = SQL\SQL::$ord.implode(',', $orderByColumn);
            } else {
                $orderByCond = SQL\SQL::$ord.$orderByColumn;
            }
        }
        if (is_null($columnsList)) {
            $cc      = $this->dblink->getColumnComma();
            $records = $this->dblink->queryToArray(SQL\SQL::$sel.'* FROM '.$cc.$tableName.$cc.' '.$limitCond.$orderByCond,
                $columnToIndex);
        } else {
            $records = $this->dblink->queryToArray(SQL\SQL::$sel.implode(',',
                    $columnsList).' FROM '.$tableName.$orderByCond.$limitCond,
                $columnToIndex);
        }

        return $records;
    }

    /**
     * Perform SQL record update.
     * Provede update záznamu do SQL.
     *
     * @param array $data
     *
     * @return int Id záznamu nebo null v případě chyby
     */
    public function updateToSQL($data = null)
    {
        if (is_null($this->myTable)) {
            return;
        }

        if (is_null($data)) {
            $data        = $this->getData();
            $useInObject = true;
        } else {
            $useInObject = false;
        }

        if (!count($data)) {
            $this->addStatusMessage(_('UpdateToSQL: Missing data'), 'error');

            return;
        }

        if (!isset($data[$this->myKeyColumn])) {
            $key = $this->getMyKey();
            if (is_null($key)) {
                $this->addStatusMessage(get_class($this).':UpdateToSQL: Unknown myKeyColumn:'.$this->myKeyColumn.' '.
                    json_encode($data), 'error');

                return;
            }
        } else {
            $key = $data[$this->myKeyColumn];
            unset($data[$this->myKeyColumn]);
        }

        if (isset($this->myLastModifiedColumn) && !isset($data[$this->myLastModifiedColumn])) {
            $data[$this->myLastModifiedColumn] = 'NOW()';
        }

        $cc       = $this->dblink->getColumnComma();
        $queryRaw = SQL\SQL::$upd.$cc.$this->myTable.$cc.' SET '.$this->dblink->arrayToSetQuery($data).SQL\SQL::$whr.$cc.$this->myKeyColumn.$cc." = '".$this->dblink->addSlashes($key)."'";
        if ($this->dblink->exeQuery($queryRaw)) {
            if ($useInObject) {
                return $this->data[$this->myKeyColumn];
            } else {
                return $key;
            }
        }

        return;
    }

    /**
     * Uloží pole dat do SQL. Pokud je $SearchForID 0 updatuje pokud ze nastaven  myKeyColumn.
     *
     * @param array $data        asociativní pole dat
     * @param bool  $searchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToSQL($data = null, $searchForID = false)
    {
        $result = null;
        if (is_null($data)) {
            $data = $this->getData();
        }

        if (count($data) < 1) {
            $this->addStatusMessage('SaveToSQL: Missing data', 'error');
        } else {
            if ($searchForID) {
                if ($this->getMyKey($data)) {
                    $rowsFound = $this->getColumnsFromSQL($this->getmyKeyColumn(),
                        [$this->getmyKeyColumn() => $this->getMyKey($data)]);
                } else {
                    $rowsFound = $this->getColumnsFromSQL([$this->getmyKeyColumn()],
                        $data);
                    if (count($rowsFound)) {
                        if (is_numeric($rowsFound[0][$this->getmyKeyColumn()])) {
                            $data[$this->getmyKeyColumn()] = (int) $rowsFound[0][$this->getmyKeyColumn()];
                        } else {
                            $data[$this->getmyKeyColumn()] = $rowsFound[0][$this->getmyKeyColumn()];
                        }
                    }
                }

                if (count($rowsFound)) {
                    $result = $this->updateToSQL($data);
                } else {
                    $result = $this->insertToSQL($data);
                }
            } else {
                if (isset($data[$this->myKeyColumn]) && !is_null($data[$this->myKeyColumn])
                    && strlen($data[$this->myKeyColumn])) {
                    $result = $this->updateToSQL($data);
                } else {
                    $result = $this->insertToSQL($data);
                }
            }
        }

        if (!is_null($result)) {
            $this->setMyKey($result);
        }

        return $result;
    }

    /**
     * Insert record to SQL database.
     * Vloží záznam do SQL databáze.
     *
     * @param array $data
     *
     * @return int id of new row in database
     */
    public function insertToSQL($data = null)
    {
        if (is_null($data)) {
            $data        = $this->getData();
            $useInObject = true;
        } else {
            $useInObject = false;
        }

        if (!count($data)) {
            $this->addStatusMessage('NO data for Insert to SQL: '.$this->myTable,
                'error');

            return;
        }

        if ($this->myCreateColumn && !isset($data[$this->myCreateColumn])) {
            $data[$this->myCreateColumn] = 'NOW()';
        }
        $queryRaw = 'INSERT INTO '.$this->dblink->getColumnComma().$this->myTable.$this->dblink->getColumnComma().' '.$this->dblink->arrayToInsertQuery($data,
                false);
        $this->dblink->useObject($this);
        if ($this->dblink->exeQuery($queryRaw)) {
            if ($useInObject) {
                $this->setMyKey($this->dblink->lastInsertID);
            }

            return $this->dblink->lastInsertID;
        }

        return;
    }

    /**
     * Smaže záznam z SQL.
     *
     * @param array|int $data
     *
     * @return bool
     */
    public function deleteFromSQL($data = null)
    {
        if (is_int($data)) {
            $data = [$this->getmyKeyColumn() => intval($data)];
        } else {
            if (is_null($data)) {
                $data = $this->getData();
            }
        }

        if (count($data)) {
            $cc = $this->dblink->getColumnComma();
            $this->dblink->exeQuery(SQL\SQL::$dlt.$cc.$this->myTable.$cc.SQL\SQL::$whr.$this->dblink->prepSelect($data));
            if ($this->dblink->getNumRows()) {
                return true;
            } else {
                return false;
            }
        } else {
            $this->addStatusMessage('DeleteFromSQL: Unknown key.', 'error');

            return false;
        }
    }

    /**
     * Assign data from field to data array.
     * Přiřadí data z políčka do pole dat.
     *
     * @param array  $data      asociativní pole dat
     * @param string $column    název položky k převzetí
     * @param bool   $mayBeNull nahrazovat chybejici hodnotu nullem ?
     * @param string $renameAs  název cílového políčka
     *
     * @return mixed převzatá do pole
     */
    public function takeToData($data, $column, $mayBeNull = false,
                               $renameAs = null)
    {
        if (isset($data[$column])) {
            if (!is_null($renameAs)) {
                $this->setDataValue($renameAs, $data[$column]);
            } else {
                $this->setDataValue($column, $data[$column]);
            }

            return $data[$column];
        } else {
            if (!is_null($mayBeNull)) {
                $this->setDataValue($column, null);

                return;
            }
        }
    }

    /**
     * Načte IDčeka z tabulky.
     *
     * @param string $tableName   jméno tabulky
     * @param string $myKeyColumn klíčovací sloupeček
     *
     * @return array list of IDs
     */
    public function getSQLList($tableName = null, $myKeyColumn = null)
    {
        if (is_null($tableName)) {
            $tableName = $this->myTable;
        }
        if (is_null($myKeyColumn)) {
            $myKeyColumn = $this->myKeyColumn;
        }
        $cc        = $this->dblink->getColumnComma();
        $listQuery = SQL\SQL::$sel.$cc.$myKeyColumn.$cc.SQL\SQL::$frm.$tableName;
        return $this->dblink->queryToArray($listQuery);
    }

    /**
     * Provede přiřazení SQL tabulky objektu.
     *
     * @param string $myTable
     */
    public function takemyTable($myTable)
    {
        $this->myTable = $myTable;
        if (!isset($this->dblink) || !is_object($this->dblink)) {
            $this->dblink = Shared::db();
        }
        $this->dblink->setTableName($myTable);
        $this->dblink->setKeyColumn($this->myKeyColumn);
    }

    /**
     * Vrací název klíčového sloupce pro SQL.
     *
     * @return string
     */
    public function getmyKeyColumn()
    {
        return $this->myKeyColumn;
    }

    /**
     * Vrací název aktuálně použivané SQL tabulky.
     *
     * @return string
     */
    public function getMyTable()
    {
        return $this->myTable;
    }

    /**
     * Vrací hodnotu klíčového políčka pro SQL.
     *
     * @param array $data data z nichž se vrací hodnota klíče
     *
     * @return int
     */
    public function getMyKey($data = null)
    {
        $key = null;
        if (is_null($data)) {
            $data = $this->getData();
        }
        if (isset($data) && isset($data[$this->myKeyColumn])) {
            $key = $data[$this->myKeyColumn];
        }

        return $key;
    }

    /**
     * Nastavuje hodnotu klíčového políčka pro SQL.
     *
     * @param int|string $myKeyValue
     *
     * @return bool
     */
    public function setMyKey($myKeyValue)
    {
        if (isset($this->myKeyColumn)) {
            $this->setDataValue($this->myKeyColumn, $myKeyValue);
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Nastaví jméno klíčového sloupečku v shopu.
     *
     * @param string $myKeyColumn
     */
    public function setmyKeyColumn($myKeyColumn)
    {
        $this->myKeyColumn = $myKeyColumn;
    }

    /**
     * Nastaví aktuální pracovní tabulku pro SQL.
     *
     * @param string $myTable
     */
    public function setmyTable($myTable)
    {
        $this->myTable = $myTable;
        $this->setObjectIdentity(['myTable' => $myTable]);
    }

    /**
     * Vrátí počet položek tabulky v SQL.
     *
     * @param string $tableName pokud není zadáno, použije se $this->myTable
     *
     * @return int
     */
    public function getSQLItemsCount($tableName = null)
    {
        if (is_null($tableName)) {
            $tableName = $this->myTable;
        }

        return $this->dblink->queryToValue(SQL\SQL::$sel.'COUNT('.$this->myKeyColumn.') FROM '.$tableName);
    }

    /**
     * Prohledá zadané slupečky.
     *
     * @param string $searchTerm
     * @param array  $columns
     */
    public function searchColumns($searchTerm, $columns)
    {
        $sTerm     = $this->dblink->addSlashes($searchTerm);
        $conditons = [];
        foreach ($columns as $column) {
            $conditons[] = '`'.$column.'` LIKE \'%'.$sTerm.'%\'';
        }

        return $this->dblink->queryToArray(SQL\SQL::$sel.'* FROM '.$this->myTable.SQL\SQL::$whr.implode(' OR ',
                    $conditons));
    }
}
