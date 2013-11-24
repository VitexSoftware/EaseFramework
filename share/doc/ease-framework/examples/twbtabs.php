<?php

/**
 * EaseFramework - vložení nové adresy do databáze
 * 
 * @author Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2013
 */

require_once 'Ease/EaseTWBootstrap.php';

/**
 * Objekt pro práci se stránkou
 * @global LQPage
 */
$oPage = new EaseTWBWebPage(_('Příklad použití tabs'));

$domainTabs = $oPage->addItem(new EaseTWBTabs('DomainTabs'));



$domainTabs->addTab("TabA", "TextA");
$domainTabs->addTab("TabB", "TextB", true);
$domainTabs->addTab("TabC", "TextC");

$oPage->draw();

