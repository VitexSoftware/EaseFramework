<?php

/**
 * Ukázka seznamů
 * 
 * @package    EaseFrameWork
 * @subpackage Exmaples
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2014 Vitex@hippy.cz (G)
 */


require_once 'Ease/EaseWebPage.php';

$oPage = new EaseWebPage();


$listA = $oPage->addItem( new EaseHtmlUlTag() );
$listA->addItemSmart('Práce malého rozsahu: 400 Kč za hodinu');
$listA->addItemSmart('Práce středního rozsahu: 1500 Kč za den');
$listA->addItemSmart('Paušální cena 30000 Kč za měsíc');

$listB = $oPage->addItem( new EaseHtmlOlTag() );
$listB->addItemSmart('Práce malého rozsahu: 500 Kč za hodinu');
$listB->addItemSmart('Práce středního rozsahu: 2000 Kč za den');
$listB->addItemSmart('Paušální cena 35000 Kč za měsíc');


$oPage->draw();

