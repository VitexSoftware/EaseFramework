<?php
/**
 * EaseFramework - vložení nové adresy do databáze.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2013
 */

namespace Ease\Example\TWB;

require_once __DIR__.'/../../vendor/autoload.php';

/*
 * Web Page
 *
 * @global WebPage
 */
$oPage = new \Ease\TWB\WebPage(_('Twitter bootrstrap Tabs Example'));

$domainTabs = $oPage->addItem(new \Ease\TWB\Tabs('Tabs'));

$domainTabs->addTab('Tab A', 'Text A');
$domainTabs->addTab('Tab B', 'Text B', true);
$domainTabs->addTab('Tab C', 'Text C');

$oPage->draw();
