<?php
/**
 * Abstraktní databázová třída.
 *
 * @deprecated since version 200
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2014 Vitex@vitexsoftware.cz (G)
 */

namespace Ease\SQL;

/**
 * Virtuálni třída pro práci s databází.
 *
 * @author Vitex <vitex@hippy.cz>
 */
abstract class SQL extends \Ease\Sand
{
    /**
     * SQL operation result handle.
     *
     * @var resource
     */
    public $result = null;

    /**
     * SQL Handle.
     *
     * @var resource
     */
    public $sqlLink = null;

    /**
     * IP serveru.
     *
     * @var string
     */
    public $server = null;

    /**
     * DB Login.
     *
     * @var string
     */
    public $username = null;

    /**
     * DB heslo.
     *
     * @var string
     */
    public $password = null;

    /**
     * Database to connect by default.
     *
     * @var string
     */
    public $database = null;

    /**
     * Database port.
     *
     * @var string
     */
    public $port = null;

    /**
     * Status připojení.
     *
     * @var bool
     */
    public $status = null;

    /**
     * Hodnota posledního voloženeho AutoIncrement sloupečku.
     *
     * @var int unsigned
     */
    public $lastIsnertID = null;

    /**
     * Poslední vykonaná SQL Query.
     *
     * @var string
     */
    public $lastQuery = '';

    /**
     * Počet ovlivněných nebo vrácených řádek při $this->LastQuery.
     *
     * @var string
     */
    public $numRows = 0;

    /**
     * Pole obsahující informace o základních paramatrech SQL přiopojení.
     *
     * @var array
     */
    public $report = ['LastMessage' => 'Please extend'];

    /**
     * Klíčový sloupeček pro SQL operace.
     *
     * @var string
     */
    public $keyColumn = '';

    /**
     * Název práve zpracovávané tabulky.
     *
     * @var string
     */
    public $tableName = '';

    /**
     * Pole obsahující strukturu SQL tabulky.
     *
     * @var array
     */
    public $tableStructure = [];

    /**
     * Poslední Chybová zpráva obdržená od SQL serveru.
     *
     * @var string
     */
    public $errorText = null;

    /**
     * Kod SQL chyby.
     *
     * @var int
     */
    public $errorNumber = null;

    /**
     * Pole obsahující výsledky posledního SQL příkazu.
     *
     * @var array
     */
    public $resultArray = [];

    /**
     * Pomocná proměnná pro datové operace.
     *
     * @var array
     */
    public $data = null;

    /**
     * Poslední zpráva obdžená od SQL serveru.
     *
     * @var string
     */
    public $lastMessage = null;

    /**
     * Prodlevy v sekundách pro znovupřipojení k databázi.
     *
     * @var array
     */
    public $reconectTimeouts = ['web' => 1, 'cgi' => 10];

    /**
     * Nastavení vlastností přípojení.
     *
     * @var array
     */
    public $connectionSettings = [];

    /**
     * Indikátor nastavení připojení - byly vykonány SET příkazy.
     *
     * @var bool
     */
    protected $connectAllreadyUP = false;

    /**
     * Type of used database.
     *
     * @var string mysql|pgsql|..
     */
    public $dbType;

    /**
     * Obecný objekt databáze.
     */
    public function __construct()
    {
        parent::__construct();
        if (!isset($this->dbType) && defined('DB_TYPE')) {
            $this->dbType = constant('DB_TYPE');
        }
        if (!isset($this->server) && defined('DB_SERVER')) {
            $this->server = constant('DB_SERVER');
        }
        if (!isset($this->username) && defined('DB_SERVER_USERNAME')) {
            $this->username = constant('DB_SERVER_USERNAME');
        }
        if (!isset($this->password) && defined('DB_SERVER_PASSWORD')) {
            $this->password = constant('DB_SERVER_PASSWORD');
        }
        if (!isset($this->database) && defined('DB_DATABASE')) {
            $this->database = constant('DB_DATABASE');
        }
        if (!isset($this->port) && defined('DB_PORT')) {
            $this->port = constant('DB_PORT');
        }
        $this->connect();
    }

    /**
     * Připojení k databázi.
     */
    public function connect()
    {
        $this->setUp();
        $this->status = true;
    }

    /**
     * Přepene databázi.
     *
     * @param type $dbName
     *
     * @return bool
     */
    public function selectDB($dbName = null)
    {
        if (!is_null($dbName)) {
            $this->database = $dbName;
        }

        return;
    }

    /**
     * Id vrácené po INSERTu.
     *
     * @return int
     */
    public function getInsertID()
    {
        return $this->lastInsertID;
    }

    /**
     * Otestuje moznost pripojeni k sql serveru.
     *
     * @param bool $succes vynucený výsledek
     *
     * @return $Success
     */
    public function ping($succes = null)
    {
        return $succes;
    }

    /**
     * Po deserializaci se znovu připojí.
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $this->connect();
    }

    /**
     * Odstraní z SQL dotazu "nebezpečné" znaky.
     *
     * @param string $queryRaw SQL Query
     *
     * @return string SQL Query
     */
    public function sanitizeQuery($queryRaw)
    {
        $sanitizedQuery = trim($queryRaw);

        return $sanitizedQuery;
    }

    public function makeReport()
    {
        $this->report['LastMessage'] = $this->lastMessage;
        $this->report['ErrorText']   = $this->errorText;
        $this->report['Database']    = $this->database;
        $this->report['Username']    = $this->username;
        $this->report['Server']      = $this->server;
    }

    /**
     * Nastaví připojení.
     *
     * @deprecated since version 210
     */
    public function setUp()
    {
        if (!$this->connectAllreadyUP) {
            if (isset($this->connectionSettings) && is_array($this->connectionSettings)
                && count($this->connectionSettings)) {
                foreach ($this->connectionSettings as $setName => $SetValue) {
                    if (strlen($setName)) {
                        $this->exeQuery("SET $setName $SetValue");
                    }
                }
                $this->connectAllreadyUP = true;
            }
        }
    }

    public function setTable($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * Vrací počet řádků vrácených nebo ovlivněným posledním sql dotazem.
     *
     * @return int počet řádků
     */
    public function getNumRows()
    {
        return $this->numRows;
    }

    /**
     * Poslední vykonaný dotaz.
     *
     * @return int počet řádků
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    /**
     * Poslední genrované ID.
     *
     * @return int ID
     */
    public function getlastInsertID()
    {
        return $this->lastInsertID;
    }

    /**
     * Vrací chybovou zprávu SQL.
     *
     * @return string
     */
    public function getLastError()
    {
        if ($this->errorText) {
            if (isset($this->errorNumber)) {
                return '#'.$this->errorNumber.': '.$this->errorText;
            } else {
                return $this->errorText;
            }
        } else {
            return;
        }
    }

    /**
     * Ověří existenci tabulky.
     *
     * @param string $tableName
     *
     * @return null|bool
     */
    public function tableExist($tableName = null)
    {
        if (!$tableName) {
            $tableName = $this->tableName;
        }
        if (!$tableName) {
            $this->error('TableExist: $TableName not known');

            return;
        }

        return true;
    }

    /**
     * Zaznamená SQL Chybu.
     *
     * @param string $title volitelný popisek, většinou název volající funkce
     */
    public function logError($title = null)
    {
        if (is_null($title)) {
            list(, $caller) = debug_backtrace(false);
            $title = $caller['function'];
        }
        if (isset($this->easeShared->User) && is_object($this->easeShared->User)) {
            return $this->easeShared->User->addStatusMessage($title.': #'.$this->errorNumber.' '.$this->errorText,
                    'error');
        } else {
            return $this->addToLog($title.': #'.$this->errorNumber.' '.$this->errorText,
                    'error');
        }
    }

    /**
     * Znovu se připojí k databázi.
     */
    public function reconnect()
    {
        $this->close();
        sleep($this->reconectTimeouts[$this->easeShared->runType]);
        $this->connect();
    }

    /**
     * Při serializaci vynuluje poslední Query.
     *
     * @return bool
     */
    public function __sleep()
    {
        $this->lastQuery = null;

        return parent::__sleep();
    }

    /**
     * Zavře databázové spojení.
     */
    public function __destruct()
    {
        if (method_exists($this, 'close')) {
            $this->close();
        }
    }

    /**
     * Vrací výsledek dotazu jako dvourozměrné pole.
     *
     * @param string $queryRaw SQL příkaz
     *
     * @return array|null
     */
    public function queryTo2DArray($queryRaw)
    {
        $result = $this->queryToArray($queryRaw);
        if (count($result)) {
            $values = [];
            foreach ($result as $value) {
                $values[] = current($value);
            }

            return $values;
        }

        return $result;
    }

    /**
     * Vrací první položku výsledku dotazu.
     *
     * @param string $queryRaw SQL příkaz vracející jednu hodnotu
     *
     * @return string|null
     */
    public function queryToValue($queryRaw)
    {
        $result = $this->queryToArray($queryRaw);
        if (count($result)) {
            return current(current($result));
        } else {
            return;
        }
    }

    /**
     * Vrací počet výsledku dotazu.
     *
     * @param string $queryRaw SQL příkaz vracející jednu hodnotu
     *
     * @return int
     */
    public function queryToCount($queryRaw)
    {
        return count($this->queryToArray($queryRaw));
    }

    /**
     * Vrací databázový objekt Pear::DB.
     *
     * @link http://pear.php.net/manual/en/package.database.mdb2.php
     *
     * @todo SET,mdb2
     *
     * @return DB|null objekt databáze
     */
    public static function &getPearObject()
    {
        include_once 'DB.php';
        $DbHelper = new DB();

        $dsn = [
            'phptype' => 'mysql', //TODO - pořešit v EaseMySQL
            'username' => DB_SERVER_USERNAME,
            'password' => DB_SERVER_PASSWORD,
            'hostspec' => DB_SERVER,
        ];

        $db = &$DbHelper->connect($dsn);

        if (PEAR::isError($db)) {
            return;
        }

        $db->query('USE '.DB_DATABASE);

        return $db;
    }

    /**
     * Vrací uvozovky pro označení sloupečků.
     *
     * @return string
     */
    public function getColumnComma()
    {
        return '';
    }

    /**
     * Return conect status.
     * 
     * @return boolean
     */
    public function isConnected()
    {
        return $this->status;
    }

    /**
     * z pole $data vytvori fragment SQL dotazu za WHERE (klicovy sloupec
     * $this->myKeyColumn je preskocen pokud neni $key false).
     *
     * @param array $data
     * @param bool  $key
     *
     * @return string
     */
    public function arrayToSetQuery($data, $key = true)
    {
        $updates = '';
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
                            $value = " '".str_replace("'", "\'", $value)."' ";
                        } else {
                            $value = " '$value' ";
                        }
                    }
                    break;
                default:
                    $value = " '$value' ";
            }

            $updates .= ' '.$this->getColumnComma().$column.$this->getColumnComma()." = $value,";
        }

        return substr($updates, 0, -1);
    }
}