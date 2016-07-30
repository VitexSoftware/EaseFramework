<?php
/**
 * Ukázková webstránka.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */

namespace Ease\Example\TWB;

require_once __DIR__.'/../../vendor/autoload.php';

/*
 * Instancujeme objekt webové stránky
 */
$oPage = new \Ease\TWB\WebPage();

$text = $oPage->getRequestValue('text');
if ($text) {
    $oPage->addStatusMessage(sprintf(_('You enter: %s as text.'), $text),
        'success');
} else {
    $oPage->addStatusMessage(_('Please enter any text'));
}

$number = $oPage->getRequestValue('number', 'int');
if ($text) {
    $oPage->addStatusMessage(sprintf(_('You enter: %s as number.'), $text),
        'success');
} else {
    $oPage->addStatusMessage(_('Please enter any number'));
}

$form = new \Ease\TWB\Form('example'); //Lets have new Form

$form->addInput(new \Ease\Html\InputTextTag('text', $text), _('Text'),
    _('Default text'), _('Text hint')); //Add Text input into

$form->addInput(new \Ease\Html\InputNumberTag('number', $number), _('Number'),
    123, _('Number hint')); //Add Text input into

$form->addItem(new \Ease\TWB\SubmitButton('ok', 'success')); //Of course ...

$formContainer = $oPage->addItem(new \Ease\TWB\Container($form)); //Enclose it

$formContainer->addItem($oPage->getStatusMessagesAsHtml());

$oPage->draw();
