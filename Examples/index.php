<?php
/**
 * Přehled ukázek.
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 */

namespace Ease;

require_once '../vendor/autoload.php';

/*
 * Instancujeme objekt webové stránky
 */
$oPage = new TWB\WebPage(\_('Ease Framework - Usage examples'));

$oPage->addItem(new Html\H1Tag(\_('Ease Framework - Usage examples')));

$tabs = new TWB\Tabs('examples');


$d     = dir('.');
while (false !== ($entry = $d->read())) {
    if (($entry[0] != '.') && $entry != 'index.php') {
        $tabs->addAjaxTab(str_replace('.php', '', $entry), urlencode($entry));
    }
}
$d->close();

$oPage->addItem(new TWB\Container($tabs));

$oPage->draw();
