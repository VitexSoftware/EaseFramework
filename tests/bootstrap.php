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
ini_set(
    'include_path', dirname(__FILE__) . '/../' . PATH_SEPARATOR . dirname(__FILE__) . '/../Ease/' . PATH_SEPARATOR . ini_get('include_path')
);

spl_autoload_register(
    function($class) {
    $filepath = "../src/{$class}.php";
    is_file($filepath) && include $filepath;
}, false, false
);
spl_autoload_register(
    function($class) {
    $filepath = "src/{$class}.php";
    is_file($filepath) && include $filepath;
}, false, false
);


require_once('PHP/Token/Stream/Autoload.php');

require_once 'Ease/EaseShared.php';
require_once 'Ease/EaseUser.php';
require_once 'Ease/EaseWebPage.php';

EaseShared::user(new EaseAnonym);
EaseShared::webPage(new EaseWebPage);

/**
 * Logovací adresář
 */
define('LOG_DIRECTORY', '/var/tmp/');

define('DB_SERVER', 'localhost');
define('DB_SERVER_PASSWORD', 'easetest');
define('DB_DATABASE', 'easetest');
define('DB_SERVER_USERNAME', 'easetest');
