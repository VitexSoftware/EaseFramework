<?php

namespace Ease\Example;

include_once '../../vendor/autoload.php';

$oPage = new \Ease\TWB4\WebPage('Twitter Bootstrap 4 Page');

$oPage->addItem(new \Ease\Html\H1Tag('Twitter Bootstrap 4'));

$oPage->addItem(new \Ease\Html\H2Tag('Alert'));


$oPage->addItem(new \Ease\TWB4\Alert('success',
    [new \Ease\Html\StrongTag('Well done!'), 'You successfully read this important alert message.']));
$oPage->addItem(new \Ease\TWB4\Alert('info',
    [new \Ease\Html\StrongTag('Heads up!'), 'This alert needs your attention, but it\'s not super important.']));
$oPage->addItem(new \Ease\TWB4\Alert('warning',
    [new \Ease\Html\StrongTag('Warning!'), 'Better check yourself, you\'re not looking too good.']));
$oPage->addItem(new \Ease\TWB4\Alert('danger',
    [new \Ease\Html\StrongTag('Oh snap!'), 'Change a few things up and try submitting again.']));

$oPage->addItem(new \Ease\Html\H2Tag('Badge'));

$types = ['default' => 'Default', 'primary' => 'Primary', 'success' => 'Success',
    'info' => 'Info', 'warning' => 'Warning', 'danger' => 'Danger'];

$oPage->addItem(new \Ease\Html\H3Tag('Simple Badge'));

foreach ($types as $type => $tmsg) {
    $oPage->addItem(new \Ease\TWB4\Badge($type, $tmsg));
}

$oPage->addItem(new \Ease\Html\H3Tag('Pill Badge'));

foreach ($types as $type => $tmsg) {
    $oPage->addItem(new \Ease\TWB4\PillBadge($type, $tmsg));
}

$oPage->draw();
