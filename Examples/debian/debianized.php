#!/usr/bin/php -q
<?php
require '/var/tmp/composer/autoload.php';

$logger = new \Ease\Logger\ToSyslog('EaseFramDemo');
$logger->addStatusMessage('Hallo world!');

$oPage = new Ease\TWB\WebPage('꩜ Debian!');


$mailer = new \Ease\Mailer('info@vitexsoftware.cz', 'Test', 'Send from debian'); 
$mailer->send();

echo $oPage->draw();
