<?php

namespace Example\Ease\Logger;

define('EASE_APPNAME', 'EaseFramework LogToEmail example');
define('EASE_LOGGER', 'console|email');
define('EASE_EMAILTO', 'info@vitexsoftware.cz');

if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php'; //Commandline
} else {
    require_once '../../vendor/autoload.php'; //Web
}

$logger = new \Ease\Sand();

$logger->addStatusMessage('Default Message', 'info');
$logger->addStatusMessage('Warning Message', 'warning');
$logger->addStatusMessage('Success Message', 'success');
$logger->addStatusMessage('Error Message', 'error');

//The eMail is send in destructor
