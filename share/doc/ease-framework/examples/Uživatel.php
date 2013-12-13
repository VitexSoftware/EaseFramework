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
$oPage = new EaseWebPage();

EaseShared::user( new EaseAnonym );
EaseShared::user()->setDataValue('email', 'vitex@hippy.cz');

$oPage->addItem(EaseShared::user());

$oPage->addItem( $oPage->getStatusMessagesAsHtml() );

$oPage->draw();
