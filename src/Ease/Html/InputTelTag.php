<?php

namespace Ease\Html;

/**
 * HTML5 tel input tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class InputTelTag extends InputTag
{

    /**
     * The <input type="tel"> is used for input fields that should contain a
     * telephone number.
     *
     * @param string $name       name
     * @param string $value      initial value
     * @param array  $properties additional properties
     */
    public function __construct($name, $value = null, $properties = [])
    {
        $properties['type']  = 'tel';
        $properties['value'] = $value;
        $properties['name']  = $name;
        parent::__construct($name, $value, $properties);
    }
}
