<?php

namespace Ease\Html;

/**
 * Html Select.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class SelectTag extends PairTag
{
    /**
     * Předvolené položka #.
     *
     * @var int
     */
    public $defaultValue = null;

    /**
     * Automaticky nastavovat název elemnetu.
     *
     * @var bool
     */
    public $setName = true;

    /**
     * @var pole hodnot k nabídnutí selectu
     */
    public $items = [];

    /**
     * Mají se vloženým položkám nastavovat ID ?
     *
     * @var bool
     */
    private $_itemsIDs = false;

    /**
     * Html select box.
     *
     * @param string $name         jmeno
     * @param array  $items        polozky
     * @param mixed  $defaultValue id predvolene polozky
     * @param array  $itemsIDs     id položek
     * @param array  $properties   tag properties
     */
    public function __construct($name, $items = null, $defaultValue = null,
                                $itemsIDs = false, $properties = [])
    {
        parent::__construct('select', $properties);
        $this->defaultValue = $defaultValue;
        $this->_itemsIDs    = $itemsIDs;
        $this->setTagName($name);
        if (is_array($items)) {
            $this->addItems($items);
        }
    }

    /**
     * Hromadné vložení položek.
     *
     * @param array $items položky výběru
     */
    public function addItems($items)
    {
        foreach ($items as $itemName => $itemValue) {
            $newItem = $this->addItem(new OptionTag($itemValue, $itemName));
            if ($this->_itemsIDs) {
                $newItem->setTagID($this->getTagName().$itemName);
            }
            if ($this->defaultValue == $itemName) {
                $this->lastItem->setDefault();
            }
        }
    }

    /**
     * Vloží hodnotu.
     *
     * @param string $value   hodnota
     * @param string $valueID id hodnoty
     */
    public function addValue($value, $valueID = 0)
    {
        $this->addItems([$valueID => $value]);
    }

    /**
     * Maketa načtení položek.
     *
     * @return array
     */
    public function loadItems()
    {
        return [];
    }

    /**
     * Nastavení hodnoty.
     *
     * @param string $value nastavovaná hodnota
     */
    public function setValue($value)
    {
        if (trim(strlen($value))) {
            foreach ($this->pageParts as $option) {
                if ($option->getValue() == $value) {
                    $option->setDefault();
                } else {
                    unset($option->tagProperties['selected']);
                }
            }
        } else {
            if (isset($this->pageParts) && count($this->pageParts)) {
                $firstItem = &reset($this->pageParts);
                $firstItem->setDefault();
            }
        }
    }

    /**
     * Vložit načtené položky.
     */
    public function finalize()
    {
        if (!count($this->pageParts)) {
            //Uninitialised Select - so we load items
            $this->addItems($this->loadItems());
        }
    }

    /**
     * Odstarní položku z nabídky.
     *
     * @param string $itemID klíč hodnoty k odstranění ze seznamu
     */
    public function delItem($itemID)
    {
        unset($this->pageParts['OptionTag@'.$itemID]);
    }
}
