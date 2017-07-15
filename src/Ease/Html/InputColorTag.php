<?php

namespace Ease\Html;

/**
 * HTML5 color input tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class InputColorTag extends InputTag
{

    /**
     * The <input type="color"> is used for input fields that should contain a color.
     *
     * @param string $name       name
     * @param string $value      initial value
     * @param array  $properties additional properties
     */
    public function __construct($name, $value = null, $properties = [])
    {
        $properties['type']  = 'color';
        $properties['value'] = $value;
        $properties['name']  = $name;
        parent::__construct($name, $value, $properties);
    }
}
