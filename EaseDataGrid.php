<?php

/**
 * Zobrazení tabulky dat
 *
 * @package    EaseFrameWork
 * @subpackage EaseHtml
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 */
class EaseDataGrid extends EaseHtmlTableTag
{

    /**
     * Naplní tabulku daty
     *
     * @param array $AllData
     */
    public function populate($AllData)
    {
        if ($this->isEmpty() && count($AllData)) {
            $this->addRowHeaderColumns(array_keys(current($AllData)));
        }
        if (count($AllData)) {
            foreach ($AllData as $Data) {
                $this->addRowColumns($Data);
            }
        }
    }

}
