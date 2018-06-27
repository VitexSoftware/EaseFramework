<?php
/**
 * Přehled ukázek.
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2018 Vitex@hippy.cz (G)
 */

namespace Ease;

define('EASE_APPNAME', 'Ease-Framework'); // So we use ../i18n/*/LC_MESSAGES/ease-framework.mo

require_once '../vendor/autoload.php';

$loc = \Ease\Shared::locale();

echo new Html\DivTag('Browser Default:'. Locale::autodetected() .' '._('Hallo'));

$loc->useLocale('en_US');

echo new Html\DivTag('en_US:'._('Hallo'));

$loc->useLocale('cs_CZ');

echo new Html\DivTag('cs_CZ:'._('Hallo'));

