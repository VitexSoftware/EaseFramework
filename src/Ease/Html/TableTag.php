<?php

namespace Ease\Html;

/**
 * HTML table.
 *
 * @author     Vitex <vitex@hippy.cz>
 */
class TableTag extends PairTag
{
    /**
     * Hlavička tabulky.
     * @var Thead
     */
    public $tHead = null;

    /**
     * Table Body
     * @var Tbody
     */
    public $tbody = null;

    /**
     * Table Foot
     * @var Tfoot 
     */
    public $tFoot = null;

    /**
     * Html Table.
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('table', $properties, $content);
        $this->tHead = $this->addItem(new Thead());
        $this->tBody = $this->addItem(new Tbody());
        $this->tFoot = $this->addItem(new Tfoot());
    }

    /**
     * @param array $headerColumns položky záhlaví tabulky
     */
    public function setHeader($headerColumns)
    {
        $this->tHead->emptyContents();
        $this->addRowHeaderColumns($headerColumns);
    }

    /**
     * Vloží do tabulky obsah pole jako buňky.
     *
     * @param array $columns    pole obsahů buňek
     * @param array $properties pole vlastností dané všem buňkám
     *
     * @return TrTag odkaz na řádku tabulky
     */
    public function &addRowColumns($columns = null, $properties = [])
    {
        $tableRow = $this->tBody->addItem(new TrTag());
        if (is_array($columns)) {
            foreach ($columns as $column) {
                if (is_object($column) && method_exists($column, 'getTagType') && $column->getTagType()
                    == 'td') {
                    $tableRow->addItem($column);
                } else {
                    $tableRow->addItem(new TdTag($column, $properties));
                }
            }
        }

        return $tableRow;
    }

    /**
     * Vloží do tabulky obsah pole jako buňky.
     *
     * @param array $columns    pole obsahů buňek
     * @param array $properties pole vlastností dané všem buňkám
     *
     * @return TrTag odkaz na řádku tabulky
     */
    public function &addRowHeaderColumns($columns = null, $properties = [])
    {
        $tableRow = $this->tHead->addItem(new TrTag());
        if (is_array($columns)) {
            foreach ($columns as $column) {
                if (is_object($column) && method_exists($column, 'getTagType') && $column->getTagType()
                    == 'th') {
                    $tableRow->addItem($column);
                } else {
                    $tableRow->addItem(new ThTag($column, $properties));
                }
            }
        }

        return $tableRow;
    }

    /**
     * Insert columns into table foot
     *
     * @param array $columns    values
     * @param array $properties options to add
     *
     * @return TrTag odkaz na řádku tabulky
     */
    public function &addRowFooterColumns($columns = null, $properties = [])
    {
        $tableRow = $this->tFoot->addItem(new TrTag());
        if (is_array($columns)) {
            foreach ($columns as $column) {
                if (is_object($column) && method_exists($column, 'getTagType') && $column->getTagType()
                    == 'th') {
                    $tableRow->addItem($column);
                } else {
                    $tableRow->addItem(new ThTag($column, $properties));
                }
            }
        }

        return $tableRow;
    }

    /**
     * Is Table Empty ?
     *
     * @param null $element je zde pouze z důvodu zpětné kompatibility
     *
     * @return type
     */
    public function isEmpty($element = null)
    {
        return $this->tBody->isEmpty();
    }

    /**
     * Populate table with given data
     *
     * @param array $contents
     */
    public function populate($contents)
    {
        foreach ($contents as $cRow) {
            $this->addRowColumns($cRow);
        }
    }
}
