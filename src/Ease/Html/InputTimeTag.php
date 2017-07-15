<?php

namespace Ease\Html;

/**
 * HTML5 time input tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class InputTimeTag extends InputTag
{

    /**
     * The <input type="time"> allows the user to select a time (no time zone).
     *
     * @param string $name       name
     * @param string $value      initial value
     * @param array  $properties additional properties
     */
    public function __construct($name, $value = null, $properties = [])
    {
        $properties['type']  = 'time';
        $properties['value'] = $value;
        $properties['name']  = $name;
        parent::__construct($name, $value, $properties);
    }
}
