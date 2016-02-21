<?php

/**
 * Ukázková webstránka
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
namespace Ease;
require_once '../vendor/autoload.php';


/**
 * Instancujeme objekt webové stránky
 */
$oPage = new WebPage();

$Text = $oPage->getRequestValue('text');
if ($Text) {
    $oPage->addStatusMessage(sprintf(_('Bylo zadáno: %s .'), $Text), 'success');
}

$form = new TWB\Form('example');
$form->addInput(new Html\InputTextTag('text'), _('Text'), _('Default text'), _('Text hint'));
$form->addItem(new TWB\SubmitButton('ok', 'success'));

$oPage->addItem($form);

$oPage->addItem($oPage->getStatusMessagesAsHtml());

$oPage->draw();
