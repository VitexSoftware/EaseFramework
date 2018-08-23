<?php
/**
 * Zaváděcí soubor pro provádění PHPUnit testů na EaseFrameworkem.
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2012-2017 Vitex@hippy.cz (G)
 */

namespace Test\Ease;

require_once __DIR__.'/../vendor/autoload.php';

if ((php_sapi_name() != 'cli') && (session_status() == 'PHP_SESSION_NONE')) {
    session_start();
} else {
    $_SESSION = [];
}

define('EASE_APPNAME', 'EaseUnitTest');
define('EASE_LOGGER', 'syslog');
define('DB_HOST', 'localhost');
define('DB_PASSWORD', 'easetest');
define('DB_DATABASE', 'easetest');
define('DB_USERNAME', 'easetest');
define('DB_PORT', 5432);
define('DB_TYPE', 'pgsql');

\Ease\Shared::user(new \Ease\Anonym());
\Ease\Shared::webPage(new \Ease\WebPage());

