<?php

/**
 * Zobrazuje vstup pro heslo s měřičem síly opatřený patřičným popiskem
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class EaseLabeledPasswordStrongInput extends EaseLabeledInput
{
    /**
     * Který input opatřit labelem ?
     * @var string EaseInputClass name
     */
    public $itemClass = 'EasePasswordInput';
}