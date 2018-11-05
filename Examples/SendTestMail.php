#!/usr/bin/php -f
<?php
/**
 * Example Mailer.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2018 Vitex@hippy.cz (G)
 */
require_once '../vendor/autoload.php';
define('EASE_LOGGER', 'console');

$bláboly = json_decode(file_get_contents('http://api.blabot.net/?version=10b&amp;dictonary=2'));

$testMail = new \Ease\Mailer(isset($argv[1]) ? $argv[1] : constant('EASE_EMAILTO'),
    'Příliš žluťoučký kůň úpěl ďábelské ódy', $bláboly->blabot->result[0]);
$testMail->addItem("\n".__FILE__);

$testMail->addFile(__FILE__);

if ($testMail->send()) {
    $testMail->addStatusMessage('Testovací mail odeslán');
} else {
    $testMail->addStatusMessage('Testovací mail nebyl odeslán', 'error');
}
