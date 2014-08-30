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
        $this->LastInsertID = null;
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
                    $this->LastInsertID = $this->sqlLink->insert_id;
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

        $status = parent::connect();
        if($status){
            $this->setUp();
        }
        return $status;
    }

    /**
     * Nastaví připojení
     * 
     * @deprecated since version 210
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
