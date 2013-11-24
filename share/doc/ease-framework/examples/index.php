<?php
/**
 * Přehled ukázek
 * 
 * @package    EaseFrameWork
 * @subpackage Exmaples
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2012 Vitex@hippy.cz (G) 
 */

require_once 'Ease/EaseWebPage.php';
require_once 'Ease/EaseJQueryWidgets.php';

/**
 * Instancujeme objekt webové stránky 
 */
$oPage = new EaseWebPage(_('Ease Framework - ukázky použití'));

$oPage->addItem(new EaseHtmlH1Tag(_('Ease Framework - ukázky použití')));

$tabs = $oPage->addItem( new EaseJQueryUITabs('priklady'));

$d = dir(".");
while (false !== ($entry = $d->read())) {
    if (($entry[0]!='.') && $entry != 'index.php'){
        $tabs->addAjaxTab(str_replace('.php','',$entry), urlencode($entry));
    }

}
$d->close();

$oPage->draw();

?>
