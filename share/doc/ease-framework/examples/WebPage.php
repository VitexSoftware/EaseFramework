<?php
/**
 * Ukázková webstránka
 * 
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G) 
 */

require_once 'Ease/EaseWebPage.php';

/**
 * Instancujeme objekt webové stránky 
 */
$oPage = new EaseWebPage();

$oPage->addStatusMessage(_('debug'),'debug');
$oPage->addStatusMessage(_('info'),'info');
$oPage->addStatusMessage(_('success'),'success');
$oPage->addStatusMessage(_('warning'),'warning');
$oPage->addStatusMessage(_('error'),'error');

$oPage->addItem( $oPage->getStatusMessagesAsHtml() );

/**
 * Vyrendrování stránky 
 */
$oPage->draw();

?>
