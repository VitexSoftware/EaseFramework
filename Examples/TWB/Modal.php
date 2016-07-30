<?php
/**
 * Twitter Bootstrap usage example page.
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2016 Vitex@hippy.cz (G)
 */

namespace Ease\Example\TWB;

require_once __DIR__.'/../../vendor/autoload.php';

$oPage = new \Ease\TWB\WebPage(_('Modal Example'));

$caution = 'Notice';

$oPage->addItem(new \Ease\TWB\Modal('caution', _('caution'), $caution,
    ['show' => true]));

$oPage->draw();
