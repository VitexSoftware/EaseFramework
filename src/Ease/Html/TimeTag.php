<?php

namespace Ease\Html;

/**
 * HTML5 time tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class TimeTag extends PairTag
{

    /**
     * Defines a date/time
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('time', $properties, $content);
    }
}
