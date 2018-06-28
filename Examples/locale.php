<?php
/**
 * Přehled ukázek.
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2018 Vitex@hippy.cz (G)
 */

namespace Ease;

session_start();

define('EASE_APPNAME', 'Ease-Framework'); // So we use ../i18n/*/LC_MESSAGES/ease-framework.mo

require_once '../vendor/autoload.php';

$loc = \Ease\Shared::locale();

echo new Html\DivTag('Default: '.Locale::$localeUsed.' '._('Hallo'));

$switcher = new Html\UlTag();
foreach ($loc->availble() as $code => $name) {
    $switcher->addItemSmart(new Html\ATag('?locale='.$code, $name));
    $code.' '.$name.'<br>';
}

echo $switcher;

$loc->useLocale('en_US');

echo new Html\DivTag('en_US:'._('Hallo'));

$loc->useLocale('cs_CZ');

echo new Html\DivTag('cs_CZ:'._('Hallo'));

