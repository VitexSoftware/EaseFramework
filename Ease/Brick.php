<?php

namespace Ease;

/**
 * Základní objekt pracující s databázemi
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
class Brick extends Sand
{

    /**
     * Objekt pro práci s SQL
     * @var EaseDbMySqli
     */
    public $dblink = null;

    /**
     * Předvolená tabulka v SQL (součást identity)
     * @var string
     */
    public $myTable = '';

    /**
     * Sql Struktura databáze. Je obsažena ve dvou podpolích $SqlStruct['ms'] a $SqlStruct['my']
     * @var array
     */
    public $sqlStruct = null;

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
     * @var User | EaseAnonym
     */
    public $user = null;

    /**
     * Multiplete DATA indicator
     *
     * @var int
     */
    protected $multipleteResult;

    /**
     * [Cs]Základní objekt pracující s databází
     */
    public function __construct()
    {
        parent::__construct();

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
     * Přiřadí objektu odkaz na objekt uživatele
     *
     * @param object|User $user         pointer to user object
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
     * @return User
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
        if (is_object($this->dblink) && is_resource($this->dblink->sqlLink)) {
            return mysql_real_escape_string($text, $this->dblink->sqlLink);
        } else {
            return parent::EaseAddSlashes($text);
        }
    }

    /**
     * Vrací z databáze sloupečky podle podmínek
     *
     * @param array            $columnsList seznam položek
     * @param array|int|string $conditions  pole podmínek nebo ID záznamu
     * @param array|string     $orderBy     třídit dle
     * @param string           $indexBy     klice vysledku naplnit hodnotou ze
     *                                      sloupečku
     * @param int $limit maximální počet vrácených záznamů
     *
     * @return array
     */
    public function getColumnsFromSQL($columnsList, $conditions = null, $orderBy = null, $indexBy = null, $limit = null)
    {
        $cc = $this->dblink->getColumnComma();
        if (($columnsList != '*') && !count($columnsList)) {
            $this->error('getColumnsFromSQL: Missing ColumnList');

            return null;
        }

        if (is_int($conditions)) {
            $conditions = array($this->getmyKeyColumn() => $conditions);
        }

        $where = '';
        if (is_array($conditions)) {
            if (!count($conditions)) {
                $this->error('getColumnsFromSQL: Missing Conditions');

                return null;
            }
            $where = ' WHERE ' . $this->dblink->prepSelect($conditions);
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
                foreach ($orderBy as $oid => $oname) {
                    $orderBy[$oid] = "`$oname`";
                }
                $orderByCond = ' ORDER BY ' . implode(',', $orderBy);
            } else {
                $orderByCond = ' ORDER BY ' . $orderBy;
            }
        } else {
            $orderByCond = '';
        }

        if ($limit) {
            $limitCond = ' LIMIT ' . $limit;
        } else {
            $limitCond = '';
        }

        if (is_array($columnsList)) {
            foreach ($columnsList as $id => $column) {
                $columnsList[$id] = $cc . $column . $cc;
            }
            return $this->dblink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $this->myTable . ' ' . $where . $orderByCond . $limitCond, $indexBy);
        } else {
            if (!strstr($columnsList, '*')) {
                $columnsList = $cc . $columnsList . $cc;
            }
            return $this->dblink->queryToArray('SELECT ' . $columnsList . ' FROM ' . $this->myTable . ' ' . $where . $orderByCond . $limitCond, $indexBy);
        }
    }

    /**
     * Načte z SQL data k aktuálnímu $ItemID
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
            $itemID = "'" . $this->easeAddSlashes($itemID) . "'";
        } else {
            $itemID = $this->easeAddSlashes($itemID);
        }
        if (is_null($itemID)) {
            $this->error('loadFromSQL: Unknown Key', $this->data);
        }
        $cc = $this->dblink->getColumnComma();
        $queryRaw = 'SELECT * FROM ' . $cc . $this->myTable . $cc . ' WHERE ' . $cc . $this->getmyKeyColumn() . $cc . '=' . $itemID;

        return $this->dblink->queryToArray($queryRaw);
    }

    /**
     * Načte z SQL data k aktuálnímu $ItemID a použije je v objektu
     *
     * @param int     $itemID     klíč záznamu
     * @param array   $dataPrefix název datové skupiny
     *
     * @return array Results
     */
    public function loadFromSQL($itemID = null)
    {
        if (is_null($itemID)) {
            $itemID = $this->getMyKey();
        }
        $SQLResult = $this->getDataFromSQL($itemID);
        $this->multipleteResult = (count($SQLResult) > 1);

        if ($this->multipleteResult) {
            $results = array();
            foreach ($SQLResult as $id => $data) {
                $this->takeData($data);
                $results[$id] = $this->getData();
            }
            $this->data = $results;
        } else {
            if (isset($SQLResult[0])) {
                $this->takeData($SQLResult[0]);
            } else {
                return null;
            }
        }
        if (count($this->data)) {
            return count($this->data);
        }
        return null;
    }

    /**
     * Vrátí z SQL všechny záznamy
     *
     * @param string $tableName     jméno tabulky
     * @param array  $columnsList   získat pouze vyjmenované sloupečky
     * @param int    $limit         SQL Limit na vracene radky
     * @param string $orderByColumn jméno sloupečku pro třídění
     * @param string $ColumnToIndex jméno sloupečku pro indexaci
     *
     * @return array
     */
    public function getAllFromSQL($tableName = null, $columnsList = null, $limit = null, $orderByColumn = null, $ColumnToIndex = null)
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
            $cc = $this->dblink->getColumnComma();
            return $this->dblink->queryToArray("SELECT * FROM " . $cc . $tableName . $cc . " " . $limitCond . $orderByCond, $ColumnToIndex);
        } else {
            return $this->dblink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $tableName . $limitCond . $orderByCond, $ColumnToIndex);
        }
    }

    /**
     * Provede update záznamu do SQL
     *
     * @param array $data
     *
     * @return int Id záznamu nebo null v případě chyby
     */
    public function updateToSQL($data = null)
    {
        if (!$this->myTable) {
            return null;
        }

        if (is_null($data)) {
            $data = $this->getData();
            $useInObject = true;
        } else {
            $useInObject = false;
        }

        if (!count($data)) {
            $this->error(_('UpdateToSQL: Missing data'));

            return null;
        }

        if (!isset($data[$this->myKeyColumn])) {
            $key = $this->getMyKey();
            if (is_null($key)) {
                $this->error(get_class($this) . ':UpdateToSQL: Unknown myKeyColumn:' . $this->myKeyColumn, $data);

                return null;
            }
        } else {
            $key = $data[$this->myKeyColumn];
            unset($data[$this->myKeyColumn]);
        }

        if (isset($this->myLastModifiedColumn) && !isset($data[$this->myLastModifiedColumn])) {
            $data[$this->myLastModifiedColumn] = 'NOW()';
        }

        $cc = $this->dblink->getColumnComma();
        $queryRaw = "UPDATE " . $cc . $this->myTable . $cc . " SET " . $this->dblink->arrayToSetQuery($data) . "  WHERE " . $cc . $this->myKeyColumn . $cc . " = '" . $this->dblink->EaseAddSlashes($key) . "'";
        if ($this->dblink->exeQuery($queryRaw)) {
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
     * Uloží pole dat do SQL. Pokud je $SearchForID 0 updatuje pokud ze nastaven  myKeyColumn
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
            $this->error('SaveToSQL: Missing data', $data);
        } else {
            if ($searchForID) {
                if ($this->getMyKey($data)) {
                    $rowsFound = $this->getColumnsFromSQL($this->getmyKeyColumn(), array($this->getmyKeyColumn() => $this->getMyKey($data)));
                } else {
                    $rowsFound = $this->getColumnsFromSQL($this->getmyKeyColumn(), $data);
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
                if (isset($data[$this->myKeyColumn]) && !is_null($data[$this->myKeyColumn]) && strlen($data[$this->myKeyColumn])) {
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
     * Vloží záznam do SQL databáze
     *
     * @param array $data
     *
     * @return id
     */
    public function insertToSQL($data = null)
    {
        if (is_null($data)) {
            $data = $this->getData();
            $useInObject = true;
        } else {
            $useInObject = false;
        }

        if (!count($data)) {
            $this->error('NO data for Insert to SQL: ' . $this->myTable);

            return null;
        }

        if ($this->myCreateColumn && !isset($data[$this->myCreateColumn])) {
            $data[$this->myCreateColumn] = 'NOW()';
        }
        $queryRaw = 'INSERT INTO ' . $this->dblink->getColumnComma() . $this->myTable . $this->dblink->getColumnComma() . ' ' . $this->dblink->arrayToInsertQuery($data, false);
        if ($this->dblink->exeQuery($queryRaw)) {
            if ($useInObject) {
                $this->setMyKey($this->dblink->lastInsertID);
            }

            return $this->dblink->lastInsertID;
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
        return $this->saveToSQL();
    }

    /**
     * Smaže záznam z SQL
     *
     * @param array|int $data
     *
     * @return bool
     */
    public function deleteFromSQL($data = null)
    {
        if (is_int($data)) {
            $data = array($this->getmyKeyColumn() => intval($data));
        } else {
            if (is_null($data)) {
                $data = $this->getData();
            }
        }

        if (count($data)) {
            $cc = $this->dblink->getColumnComma();
            $this->dblink->exeQuery('DELETE FROM ' . $cc . $this->myTable . $cc . ' WHERE ' . $this->dblink->prepSelect($data));
            if ($this->dblink->getNumRows()) {
                return true;
            } else {
                return false;
            }
        } else {
            $this->error('DeleteFromSQL: Unknown key.', $data);

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
     * Načte IDčeka z tabulky
     *
     * @param string $tableName   jméno tabulky
     * @param string $myKeyColumn klíčovací sloupeček
     *
     * @return int počet položek
     */
    public function getSQLList($tableName = null, $myKeyColumn = null)
    {
        if (!$tableName) {
            $tableName = $this->myTable;
        }
        if (!$myKeyColumn) {
            $myKeyColumn = $this->myKeyColumn;
        }
        $cc = $this->dblink->getColumnComma();
        $listQuery = "SELECT $cc" . $myKeyColumn . "$cc FROM $tableName ";

        $this->dblink->queryToArray($listQuery);
        $this->DataIdList = $this->dblink->resultArray;

        return count($this->DataIdList);
    }

    /**
     * Provede přiřazení SQL tabulky objektu
     *
     * @param string $myTable
     */
    public function takemyTable($myTable = null)
    {
        if ($myTable) {
            $this->myTable = $myTable;
        }
        if (!isset($this->dblink) || !is_object($this->dblink)) {
            $this->dblink = SQL\PDO::singleton();
        }
        $this->dblink->setTableName($myTable);
        $this->dblink->setKeyColumn($this->myKeyColumn);
    }

    /**
     * Vrací název klíčového sloupce pro SQL
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
        return $this->dblink->queryToValue('SELECT COUNT(*) FROM ' . $this->myTable . ' WHERE ' . $this->getmyKeyColumn() . '=' . intval($id));
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
     * Vrací název aktuálně použivané SQL tabulky
     *
     * @return string
     */
    public function getMyTable()
    {
        return $this->myTable;
    }

    /**
     * Vrací hodnotu klíčového políčka pro SQL
     *
     * @param array $data data z nichž se vrací hodnota klíče
     *
     * @return int
     */
    public function getMyKey($data = null)
    {
        if (!$data) {
            $data = $this->getData();
        }
        if (isset($data) && isset($data[$this->myKeyColumn])) {
            return $data[$this->myKeyColumn];
        }

        return null;
    }

    /**
     * Nastavuje hodnotu klíčového políčka pro SQL
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
     * Nastaví jméno klíčového sloupečku v shopu
     *
     * @param string $myKeyColumn
     */
    public function setmyKeyColumn($myKeyColumn)
    {
        $this->myKeyColumn = $myKeyColumn;
    }

    /**
     * Nastaví aktuální pracovní tabulku pro SQL
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
     * Test na existenci tabulky v SQL databázi
     *
     * @param string $tableName
     *
     * @return bool
     */
    public function mySQLTableExist($tableName = null)
    {
        if (!$tableName) {
            $tableName = $this->myTable;
        }
        if (!$tableName) {
            $this->error('ShopTableExist: $TableName not known', $this->identity);
        }

        return $this->dblink->tableExist($tableName);
    }

    /**
     * Vrátí počet položek tabulky v SQL
     *
     * @param string $tableName pokud není zadáno, použije se $this->myTable
     *
     * @return int
     */
    public function getSQLItemsCount($tableName = null)
    {
        if (!$tableName) {
            $tableName = $this->myTable;
        }

        return $this->dblink->queryToValue('SELECT COUNT(' . $this->myKeyColumn . ') FROM ' . $tableName);
    }

    /**
     * Pouze malé a velké písmena
     * @return string text bez zvláštních znaků
     */
    public static function lettersOnly($text)
    {
        return preg_replace('/[^(a-zA-Z0-9)]*/', '', $text);
    }

    /**
     * Prohledá zadané slupečky
     *
     * @param string $searchTerm
     * @param array $columns
     */
    public function searchColumns($searchTerm, $columns)
    {
        $sTerm = $this->dblink->AddSlashes($searchTerm);
        $conditons = array();
        foreach ($columns as $column) {
            $conditons[] = '`' . $column . '` LIKE \'%' . $sTerm . '%\'';
        }
        return $this->dblink->queryToArray('SELECT * FROM ' . $this->myTable . ' WHERE ' . implode(' OR ', $conditons));
    }

}
