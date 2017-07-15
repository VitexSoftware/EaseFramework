<?php

namespace Ease\Html;

/**
 * HTML5 url input tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class InputUrlTag extends InputTag
{

    /**
     * The <input type="url"> is used for input fields that should contain a
     * URL address.
     *
     * @param string $name       name
     * @param string $value      initial value
     * @param array  $properties additional properties
     */
    public function __construct($name, $value = null, $properties = [])
    {
        $properties['type']  = 'url';
        $properties['value'] = $value;
        $properties['name']  = $name;
        parent::__construct($name, $value, $properties);
    }
}
