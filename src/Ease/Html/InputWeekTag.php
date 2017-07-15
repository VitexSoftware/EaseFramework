<?php

namespace Ease\Html;

/**
 * HTML5 week input tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class InputWeekTag extends InputTag
{

    /**
     * The <input type="week"> allows the user to select a week and year.
     *
     * @param string $name       name
     * @param string $value      initial value
     * @param array  $properties additional properties
     */
    public function __construct($name, $value = null, $properties = [])
    {
        $properties['type']  = 'week';
        $properties['value'] = $value;
        $properties['name']  = $name;
        parent::__construct($name, $value, $properties);
    }
}
