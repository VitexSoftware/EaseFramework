<?php

namespace Ease\Html;

/**
 * Group of CheckBoxes
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class CheckboxGroup extends EaseInputContainer
{

    public $itemClass = 'CheckboxTag';

    /**
     * Pocet vlozenych polozek
     * @var int
     */
    private $_subitemCount = 0;

    /**
     * Pole hodnot k nastavení
     * @var array
     */
    public $values = [];

    /**
     * Skupina checkboxů
     *
     * @param string $name
     * @param array  $items
     * @param array  $itemValues
     * @param array  $tagProperties
     */
    public function __construct($name, $items = null, $itemValues = null, $tagProperties = null)
    {
        parent::__construct($name, $items, $tagProperties);
        if (!is_null($itemValues)) {
            $values = [];
            foreach ($itemValues as $itemName => $item) {
                $values[$name . '_' . $itemName] = $item;
            }
            $this->setValues($values);
        }
    }

    /**
     * Přejmenuje vložené checkboxy pro použití ve formuláři
     *
     * @param CheckboxTag $pageItem     vkládaný objekt CheckBoxu
     * @param string              $pageItemName Pod tímto jménem je objekt vkládán do stromu
     *
     * @return CheckboxTag
     */
    function &addItem($pageItem, $pageItemName = null)
    {
        /**
         * Allready Added Item
         * @var CheckboxTag
         */
        $itemInpage = parent::addItem($pageItem);
        if (is_object($itemInpage)) {
            if (isset($this->items)) {
                $keys = array_keys($this->items);
                $itemInpage->setTagProperties(['name' => $itemInpage->getTagProperty('name') . '#' . $keys[$this->_subitemCount]]);
                if (isset($this->values[$keys[$this->_subitemCount]])) {
                    $itemInpage->setValue((bool) $this->values[$keys[$this->_subitemCount]]);
                }
                next($this->items);
                $this->_subitemCount++;
            }
        }
        return $itemInpage;
    }

    /**
     * Vložení jména skupiny
     */
    public function finalize()
    {
        parent::finalize();
        parent::addItem(new InputHiddenTag('CheckBoxGroups[' . $this->name . ']', $this->getTagName()));
    }

    /**
     * Poprvé nastaví hodnotu checkboxu. Druhé volání nastavuje příznak checked,
     * pokud je hodnota stejná jako již nabitá
     *
     * @param string $value vracená hodnota
     *
     * @todo boolean $Automatic zabraňuje mazání textu z tlačítek v objektu SubmitButton
     */
    public function setValue($value)
    {
        $CurrentValue = $this->GetTagProperty('value');
        if ($CurrentValue) {
            if ($CurrentValue == $value) {
                $this->setTagProperties(['checked']);
            }
        } else {
            $this->setTagProperties(['value' => $value]);
        }
    }

    /**
     * Nastaví hodnoty položek
     *
     * @param array $Values pole hodnot
     */
    public function setValues($Values)
    {
        $TagName = $this->getTagName();
        foreach (array_keys($this->items) as $ItemKey) {
            if (isset($Values[$TagName . '_' . $ItemKey])) {
                $this->values[$ItemKey] = $Values[$TagName . '_' . $ItemKey];
            }
        }
    }

}
