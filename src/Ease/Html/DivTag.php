<?php

namespace Ease\Html;

/**
 * HTML Div tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class DivTag extends PairTag
{
    /**
     * Simple Div tag
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('div', $properties, $content);
    }
}
