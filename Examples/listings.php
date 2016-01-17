<?php

/**
 * Listings Example
 * 
 * @package    EaseFrameWork
 * @subpackage Exmaples
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2014 Vitex@hippy.cz (G)
 */
namespace Ease;
require_once '../vendor/autoload.php';



$oPage = new WebPage();

$listA =  new Html\UlTag();
$listA->addItemSmart(_('Ul One'));
$listA->addItemSmart(_('Ul Two'));
$listA->addItemSmart(_('Ul Three'));

$listB = new Html\OlTag();
$listB->addItemSmart(_('Ol One'));
$listB->addItemSmart(_('Ol Two'));
$listB->addItemSmart(_('Ol Three'));

$row = new TWB\Row();
$row->addColumn(4,$listA);
$row->addColumn(4,$listB);

$oPage->addItem( new TWB\Container( $row ) );

$oPage->draw();

