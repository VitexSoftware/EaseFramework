<?php

/**
 * Zaváděcí soubor pro provádění PHPUnit testů na EaseFrameworkem
 *
 * @package    EaseUnitTests
 * @subpackage UnitTests
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */

if (!isset($_SESSION)) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';


//require_once('PHP/Token/Stream/Autoload.php');

\Ease\Shared::user(new Ease\Anonym);
\Ease\Shared::webPage(new Ease\WebPage);

/**
 * Logovací adresář
 */
define('LOG_DIRECTORY', '/var/tmp/');

define('DB_SERVER', 'localhost');
define('DB_SERVER_PASSWORD', 'easetest');
define('DB_DATABASE', 'easetest');
define('DB_SERVER_USERNAME', 'easetest');
define('DB_TYPE', 'pgsql');
