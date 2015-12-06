<?php

/**
 * EaseFramework - vložení nové adresy do databáze
 *
 * @author Vitex <vitex@hippy.cz>
 * @copyright Vitex@hippy.cz (G) 2013
 */

require_once 'Ease/Ease\TWB\ootstrap.php';

/**
 * Objekt pro práci se stránkou
 * @global LQPage
 */
$oPage = new Ease\TWB\WebPage(_('Příklad použití tabs'));

$domainTabs = $oPage->addItem(new Ease\TWB\Tabs('DomainTabs'));

$domainTabs->addTab("TabA", "TextA");
$domainTabs->addTab("TabB", "TextB", true);
$domainTabs->addTab("TabC", "TextC");

$oPage->draw();
