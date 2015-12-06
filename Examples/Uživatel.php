<?php
/**
 * Ukázková webstránka
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G)
 */

require_once 'Ease/EaseWebPage.php';
require_once 'Ease/EaseUser.php';

/**
 * Instancujeme objekt webové stránky
 */
$oPage = new Ease\WebPage();

Ease\Shared::user( new Ease\Anonym );
Ease\Shared::user()->setDataValue('email', 'vitex@hippy.cz');

$oPage->addItem(Ease\Shared::user());

$oPage->addItem( $oPage->getStatusMessagesAsHtml() );

$oPage->draw();
