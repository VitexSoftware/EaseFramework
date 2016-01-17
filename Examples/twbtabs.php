<?php

/**
 * EaseFramework - vložení nové adresy do databáze
 *
 * @author Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2013
 */

namespace Ease;
require_once '../vendor/autoload.php';

/**
 * Web Page
 * @global TWB\WebPage
 */
$oPage = new TWB\WebPage(_('Twitter bootrstrap Tabs Example'));

$domainTabs = $oPage->addItem(new TWB\Tabs('DomainTabs'));

$domainTabs->addTab("TabA", "TextA");
$domainTabs->addTab("TabB", "TextB", true);
$domainTabs->addTab("TabC", "TextC");

$oPage->draw();
