<?php
/**
 * Example WebPage for TwitterBootstrap.
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2016 Vitex@hippy.cz (G)
 */

namespace Ease\Example\TWB;

require_once __DIR__.'/../../vendor/autoload.php';

/*
 * New web page instance
 */
$oPage = new AppWebPage(_('Twitter Bootstrap'));

$oPage->addStatusMessage(_('debug'), 'debug');
$oPage->addStatusMessage(_('info'), 'info');
$oPage->addStatusMessage(_('success'), 'success');
$oPage->addStatusMessage(_('warning'), 'warning');
$oPage->addStatusMessage(_('error'), 'error');

$oPage->addItem(new \Ease\TWB\LinkButton('./', _('Back to examples'), 'info'));

/*
 * Page rendering
 */
$oPage->draw();
