<?php

/**
 * Ukázková webstránka
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
require_once 'Ease/EaseWebPage.php';
require_once 'Ease/EaseHtmlForm.php';
require_once 'Ease/EaseJQueryWidgets.php';

/**
 * Instancujeme objekt webové stránky
 */
$oPage = new EaseWebPage();

$Text = $oPage->getRequestValue('text');
if ($Text) {
    $oPage->addStatusMessage(sprintf(_('Bylo zadáno: %s .'), $Text), 'success');
}

$Form = new EaseHtmlForm('example');
$Form->addItem(new EaseLabeledTextInput('text', 'text', 'text'));
$Form->addItem(new EaseJQuerySubmitButton('ok', 'ok'));

$oPage->addItem($Form);

$oPage->addItem($oPage->getStatusMessagesAsHtml());

$oPage->draw();
