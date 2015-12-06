<?php

/**
 * Zobrazuje select, opatřený patřičným popiskem
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledSelect extends EaseLabeledInput
{
    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name
     */
    public $itemClass = 'Ease\Html\Select';
    /**
     * Vložený select
     * @var Ease\Html\Select
     */
    public $enclosedElement = NULL;
    /**
     * Hromadné vložení položek
     *
     * @param array $items položky výběru
     */
    public function addItems($items)
    {
        return $this->enclosedElement->addItems($items);
    }
}