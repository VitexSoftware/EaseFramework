<?php

/**
 * Zobrazuje vstup kontrolu hesla s indikátorem souhlasu, opatřený patřičným
 * popiskem
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledPasswordControlInput extends EaseLabeledInput
{
    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name
     */
    public $itemClass = 'EasePasswordControlInput';
}