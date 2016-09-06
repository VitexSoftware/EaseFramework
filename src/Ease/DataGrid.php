<?php
/**
 * Zobrazení tabulky dat.
 *
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright  2009-2012 Vitex@hippy.cz (G)
 */

namespace Ease;

class DataGrid extends Html\TableTag
{

    /**
     * Datagrid.
     *
     * @param mixed $content
     * @param array $properties
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct(null, $properties);
        if (is_array($content)) {
            $this->populate($content);
        }
    }

    /**
     * Naplní tabulku daty.
     *
     * @param array $allData
     */
    public function populate($allData)
    {
        if ($this->isEmpty() && count($allData)) {
            $this->addRowHeaderColumns(array_keys(current($allData)));
        }
        if (count($allData)) {
            foreach ($allData as $data) {
                $this->addRowColumns($data);
            }
        }
    }
}