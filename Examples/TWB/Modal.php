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


//$oPage->addItem('<button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bs-example-modal-sm">Small modal</button>
//
//<div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
//  <div class="modal-dialog modal-sm">
//    <div class="modal-content">
//      ...
//    </div>
//  </div>
//</div>');
        
$oPage->addItem( new Modal('caution',_('caution'),$caution,['show'=>true]) );

$oPage->draw();
