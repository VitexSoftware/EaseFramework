<?php

namespace Ease\TWB;

class CheckBoxGroup extends Ease\Container
{

    /**
     *
     * @param array $items
     */
    public $items = array();

    function __construct($items)
    {
        $this->items = $items;
    }

    function finalize()
    {
        foreach ($this->items as $name => $value) {
            $this->addItem(new Checkbox($name, $value, $value, $checked));
        }
    }

}
