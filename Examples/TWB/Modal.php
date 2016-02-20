<?php
/**
 * Ukázková webstránka pro TwitterBootstrap
 *
 * @package    EaseFrameWork
 * @subpackage Examples
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 */
namespace Ease\TWB;

require_once '../../vendor/autoload.php';


$oPage = new WebPage(_('Modal Example'));

Part::twBootstrapize($oPage);

$caution = 'Notice';
     
$oPage->addItem( new Modal('caution',_('caution'),$caution,['show'=>true]) );

$oPage->draw();
