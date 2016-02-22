<?php

/**
 * Ukázková webstránka.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */
namespace Ease;

require_once '../vendor/autoload.php';

/*
 * Instancujeme objekt webové stránky
 */
$oPage = new WebPage();

Shared::user(new Anonym());
Shared::user()->setDataValue('email', 'vitex@hippy.cz');

$oPage->addItem(Shared::user());

$oPage->addItem($oPage->getStatusMessagesAsHtml());

$oPage->draw();
