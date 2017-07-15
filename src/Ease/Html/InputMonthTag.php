<?php

namespace Ease\Html;

/**
 * HTML5 month input tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class InputMonthTag extends InputTag
{

    /**
     * The <input type="month"> allows the user to select a month and year.
     *
     * @param string $name       name
     * @param string $value      initial value
     * @param array  $properties additional properties
     */
    public function __construct($name, $value = null, $properties = [])
    {
        $properties['type']  = 'month';
        $properties['value'] = $value;
        $properties['name']  = $name;
        parent::__construct($name, $value, $properties);
    }
}
