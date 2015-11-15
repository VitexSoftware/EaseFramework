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
     * Funkční sloupečky pro My
     * @var array
     */
    public $myDbRoles = null;

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
            $key = $this->getMyKey();
            if ($key) {
                return parent::setObjectName(get_class($this) . '@' . $key);
            } else {
                return parent::setObjectName();
            }
        }
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
                case 'email':                    // Obalka
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
                $columnsList[$id] = '`' . $column . '`';
            }
            return $this->myDbLink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $this->myTable . ' ' . $where . $orderByCond . $limitCond, $indexBy);
        } else {
            if (!strstr($columnsList, '*')) {
                $columnsList = '`' . $columnsList . '`';
            }
            return $this->myDbLink->queryToArray('SELECT ' . $columnsList . ' FROM ' . $this->myTable . ' ' . $where . $orderByCond . $limitCond, $indexBy);
        }
    }

    /**
     * Načte z MySQL data k aktuálnímu $ItemID
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

        $queryRaw = 'SELECT * FROM `' . $this->myTable . '` WHERE `' . $this->getmyKeyColumn() . '`=' . $itemID;

        return $this->myDbLink->queryToArray($queryRaw);
    }

    /**
     * Načte z MySQL data k aktuálnímu $ItemID a použije je v objektu
     *
     * @param int     $itemID     klíč záznamu
     * @param boolean $multiplete nevarovat v případě více výsledků
     *
     * @return array Results
     */
    public function loadFromSQL($itemID = null, $multiplete = false)
    {
        $mySQLResult = $this->getDataFromSQL($itemID);
        if ($multiplete) {
            $this->data = $mySQLResult;
        } else {
            if (count($mySQLResult) > 1) {
                $this->error('Multipete Query result: ' . $this->myDbLink->getLastQuery());
            }
            if (isset($mySQLResult[0])) {
                $this->data = $mySQLResult[0];
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
            return $this->myDbLink->queryToArray("SELECT * FROM `" . $tableName . "` " . $limitCond . $orderByCond, $ColumnToIndex);
        } else {
            return $this->myDbLink->queryToArray('SELECT ' . implode(',', $columnsList) . ' FROM ' . $tableName . $limitCond . $orderByCond, $ColumnToIndex);
        }
    }

    /**
     * Oznámí MySQL objektu vlastnosti predvolene tabulky
     *
     * @deprecated since version 200
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
     * Provede update záznamu do MySQL
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
            $data = $this->getData();
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
                $this->error(get_class($this) . ':UpdateToMySQL: Unknown myKeyColumn:' . $this->myKeyColumn, $data);

                return null;
            }
        } else {
            $key = $data[$this->myKeyColumn];
            unset($data[$this->myKeyColumn]);
        }

        if (isset($this->myLastModifiedColumn) && !isset($data[$this->myLastModifiedColumn])) {
            $data[$this->myLastModifiedColumn] = 'NOW()';
        }


        $queryRaw = "UPDATE `" . $this->myTable . "` SET " . $this->myDbLink->arrayToQuery($data) . "  WHERE `" . $this->myKeyColumn . "` = '" . $this->myDbLink->EaseAddSlashes($key) . "'";
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
    public function saveToSQL($data = null, $searchForID = false)
    {
        if (is_object($this->myDbLink)) {
            return $this->saveToMySQL($data, $searchForID);
        }

        $this->addStatusMessage(_('Databáze není definována'), 'error');
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
        $result = null;
        if (is_null($data)) {
            if (array_key_exists('MySQL', $this->data)) {
                $data = $this->getData('MySQL');
            } else {
                $data = $this->getData();
            }
        }

        if (count($data) < 1) {
            $this->error('SaveToMySQL: Missing data', $data);
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
        if (is_object($this->myDbLink)) {
            return $this->insertToMySQL($data);
        }

        $this->addStatusMessage(_('Databáze není definována'), 'error');
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
        $result = array();
        if (is_object($this->myDbLink)) {
            $result['my'] = $this->saveToMySQL();
        }

        return $result;
    }

    /**
     * Smaže záznam z MySQL
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
            $this->myDbLink->exeQuery('DELETE FROM `' . $this->myTable . '` WHERE ' . $this->myDbLink->prepSelect($data));
            if ($this->myDbLink->getNumRows()) {
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
     * Vrací název aktuálně použivané SQL tabulky
     *
     * @return string
     */
    public function getMyTable()
    {
        return $this->myTable;
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
     * Test na existenci tabulky v MySQL databázi
     *
     * @param string $tableName
     *
     * @return bool
     */
    public function mySQLTableExist($tableName = null)
    {
        $existence = null;
        if (!$tableName) {
            $tableName = $this->myTable;
        }
        if (!$tableName) {
            $this->error('TableExist: $TableName not set', $this->identity);
        }

        if (is_object($this->myDbLink)) {
            $existence = $this->myDbLink->tableExist($tableName);
        } else {
            $existence = null;
        }
        return $existence;
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
        $sTerm = $this->myDbLink->AddSlashes($searchTerm);
        $conditons = array();
        foreach ($columns as $column) {
            $conditons[] = '`' . $column . '` LIKE \'%' . $sTerm . '%\'';
        }
        return $this->myDbLink->queryToArray('SELECT * FROM ' . $this->myTable . ' WHERE ' . implode(' OR ', $conditons));
    }

}
