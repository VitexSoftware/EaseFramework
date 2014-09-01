<?php
/**
 * Zaváděcí soubor pro provádění PHPUnit testů na EaseFrameworkem
 *
 * @package    EaseUnitTests
 * @subpackage UnitTests
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
*/


session_start();


require_once 'Ease/EaseBrick.php';
require_once 'Ease/EaseUser.php';
require_once 'Ease/EaseSQL.php';
require_once 'Ease/EaseAnonym.php';
require_once 'Ease/EaseWebPage.php';


EaseShared::user( new EaseAnonym );
EaseShared::webPage( new EaseWebPage );

/**
 * Logovací adresář
 */
define('LOG_DIRECTORY','/var/tmp/');

define('DB_SERVER', 'localhost'); define('DB_SERVER_PASSWORD', 'triband'); define('DB_DATABASE', 'EaseShop'); define('DB_SERVER_USERNAME', 'triband');
define('MS_DB_SERVER', 'mssql.murka.cz:1433'); define('MS_DB_SERVER_USERNAME', 'sa'); define('MS_DB_SERVER_PASSWORD', '_sql0206'); define('MS_DB_DATABASE', 'StwPh_26685337_2011');


