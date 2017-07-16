<?php

namespace Example\Ease\Logger;

define('EASE_LOGGER', 'console|syslog');

require_once '../vendor/autoload.php';

$logger = new \Ease\Sand();

$logger->addStatusMessage('Mail Message', 'mail');
$logger->addStatusMessage('Debug Message', 'debug');
$logger->addStatusMessage('Default Message', 'info');
$logger->addStatusMessage('Warning Message', 'warning');
$logger->addStatusMessage('Success Message', 'success');
$logger->addStatusMessage('Error Message', 'error');

