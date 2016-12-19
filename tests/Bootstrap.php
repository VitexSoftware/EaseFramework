<?php
/**
 * Zaváděcí soubor pro provádění PHPUnit testů na EaseFrameworkem.
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012 Vitex@hippy.cz (G)
 */
if (!isset($_SESSION)) {
    session_start();
}

require __DIR__.'/../vendor/autoload.php';

define('EASE_LOGGER', 'syslog');
define('DB_SERVER', 'localhost');
define('DB_SERVER_PASSWORD', 'easetest');
define('DB_DATABASE', 'easetest');
define('DB_SERVER_USERNAME', 'easetest');
define('DB_PORT', 5432);
define('DB_TYPE', 'pgsql');

//require_once('PHP/Token/Stream/Autoload.php');

\Ease\Shared::user(new Ease\Anonym());
\Ease\Shared::webPage(new Ease\WebPage());
