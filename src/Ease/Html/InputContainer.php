<?php

namespace Ease\Html;

/**
 * Skupina vstupních prvků.
 *
 * @deprecated since version 1.0
 *
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 */
class InputContainer extends \Ease\Container
{
    /**
     * Name of Radios.
     *
     * @var string
     */
    public $name = 'container';

    /**
     * Stored values.
     *
     * @var array
     */
    public $items = [];

    /**
     * Default value.
     *
     * @var mixed
     */
    public $checked = null;

    /**
     * ClassName.
     *
     * @var InputTag or childs
     */
    public $itemClass = 'InputTextTag';

    /**
     * Skupina inputů.
     *
     * @param string $name          výchozí jméno tagů
     * @param array  $items         pole položek
     * @param string $tagProperties parametry tagů
     */
    public function __construct($name, $items = [], $tagProperties = null)
    {
        parent::__construct();
        $this->name  = $name;
        $this->items = $items;
    }

    /**
     * Nastaví hodnotu vstupního políčka.
     *
     * @param string $value hodnota
     */
    public function setValue($value)
    {
        $this->checked = $value;
    }

    /**
     * Vrací hodnotu vstupního políčka.
     *
     * @param bool $value hodnota je ignorována
     *
     * @return string $value binární hodnota - stav
     */
    public function getValue($value)
    {
        return $this->checked;
    }

    /**
     * Return assigned form input Tag name.
     *
     * @return string
     */
    public function getTagName()
    {
        return $this->name;
    }

    /**
     * Vloží podprvky.
     */
    public function finalize()
    {
        $itemID = 1;
        foreach ($this->items as $value => $caption) {
            if ($this->checked == $value) {
                $this->addItem(new $this->itemClass($this->name, $value,
                        ['checked']));
            } else {
                $this->addItem(new $this->itemClass($this->name, $value));
            }
            $this->lastItem->setTagID($this->name.$itemID++);
            $this->addLabel($caption);
        }
        $this->finalized = true;
    }

    /**
     * Doplní popisek prvku.
     *
     * @param string $label text popisku
     */
    public function addLabel($label = null)
    {
        $forID = $this->lastItem->getTagID();
        if (is_null($label)) {
            $label = $forID;
        }
        $this->addItem('<label for="'.$forID.'">'.$label.'</label>');
    }
}
