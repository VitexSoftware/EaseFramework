<?php

namespace Ease\Html;

/**
 * HTML table
 *
 * @subpackage Ease\Html\
 * @author     Vitex <vitex@hippy.cz>
 */
class TableTag extends Ease\Html\PairTag
{

    /**
     * Hlavička tabulky
     * @var Ease\Html\Thead
     */
    public $tHead = null;

    /**
     * Tělo tabulky
     * @var Ease\Html\Tbody
     */
    public $tbody = null;

    /**
     * Html Tabulka
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('table', $properties, $content);
        $this->tHead = $this->addItem(new Ease\Html\Thead());
        $this->tBody = $this->addItem(new Ease\Html\Tbody());
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
     * Vloží do tabulky obsah pole jako buňky
     *
     * @param array $columns    pole obsahů buňek
     * @param array $properties pole vlastností dané všem buňkám
     *
     * @return Ease\Html\TrTag odkaz na řádku tabulky
     */
    function &addRowColumns($columns = null, $properties = null)
    {
        $tableRow = $this->tBody->addItem(new Ease\Html\TrTag());
        if (is_array($columns)) {
            foreach ($columns as $column) {
                if (is_object($column) && method_exists($column, 'getTagType') && $column->getTagType() == 'td') {
                    $tableRow->addItem($column);
                } else {
                    $tableRow->addItem(new Ease\Html\TdTag($column, $properties));
                }
            }
        }
        return $tableRow;
    }

    /**
     * Vloží do tabulky obsah pole jako buňky
     *
     * @param array $columns    pole obsahů buňek
     * @param array $properties pole vlastností dané všem buňkám
     *
     * @return Ease\Html\TrTag odkaz na řádku tabulky
     */
    function &addRowHeaderColumns($columns = null, $properties = null)
    {
        $tableRow = $this->tHead->addItem(new Ease\Html\TrTag());
        if (is_array($columns)) {
            foreach ($columns as $column) {
                if (is_object($column) && method_exists($column, 'getTagType') && $column->getTagType() == 'th') {
                    $tableRow->addItem($column);
                } else {
                    $tableRow->addItem(new Ease\Html\ThTag($column, $properties));
                }
            }
        }
        return $tableRow;
    }

    /**
     * Je tabulka prázdná ?
     *
     * @param null $element je zde pouze z důvodu zpětné kompatibility
     * @return type
     */
    function isEmpty($element = null)
    {
        return $this->tBody->isEmpty();
    }

    /**
     * Naplní tabulku daty
     *
     * @param array $contents
     */
    function populate($contents)
    {
        foreach ($contents as $cRow) {
            $this->addRowColumns($cRow);
        }
    }

}
