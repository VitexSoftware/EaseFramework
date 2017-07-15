<?php

namespace Ease\Html;

/**
 * HTML5 datetime input tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class InputDateTimeTag extends InputTag
{

    /**
     * The <input type="date"> is used for input fields that should contain a date and time.
     *
     * @param string $name       name
     * @param string $value      initial value
     * @param array  $properties additional properties
     */
    public function __construct($name, $value = null, $properties = [])
    {
        $properties['type']  = 'datetime';
        $properties['value'] = $value;
        $properties['name']  = $name;
        parent::__construct($name, $value, $properties);
    }
}
