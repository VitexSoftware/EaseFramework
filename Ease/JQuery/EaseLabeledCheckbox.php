<?php

/**
 * Zobrazuje checkbox, opatřený patřičným popiskem
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledCheckbox extends EaseLabeledInput
{
    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name
     */
    public $itemClass = 'Ease\Html\CheckboxTag';
}