<?php

namespace Ease\Html;

/**
 * HTML5 date input tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class InputDateTag extends InputTag
{

    /**
     * The <input type="date"> is used for input fields that should contain a date.
     *
     * @param string       $name       input name
     * @param string|\Date $value      initial value as string or DateTime 
     * @param array        $properties additional properties
     */
    public function __construct($name, $value = null, $properties = [])
    {
        $properties['type']  = 'date';
        $properties['value'] = is_object($value) ? $value->format('Y-m-d') : $value;
        $properties['name']  = $name;
        parent::__construct($name, $properties['value'], $properties);
    }
}
