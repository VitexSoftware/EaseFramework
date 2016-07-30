<?php
/**
 * WebPage example.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2016 Vitex@hippy.cz (G)
 */
require_once '../vendor/autoload.php';

/*
 * Instancujeme objekt webové stránky
 */
$oPage = new Ease\WebPage();

$oPage->addStatusMessage(_('debug'), 'debug');
$oPage->addStatusMessage(_('info'), 'info');
$oPage->addStatusMessage(_('success'), 'success');
$oPage->addStatusMessage(_('warning'), 'warning');
$oPage->addStatusMessage(_('error'), 'error');

$oPage->addItem(new Ease\Html\H1Tag(_('Example Web Page')));

$oPage->addItem(new Ease\Html\FieldSet(_('Status messages'),
    $oPage->getStatusMessagesAsHtml()));

/*
 * Vyrendrování stránky
 */
$oPage->draw();
