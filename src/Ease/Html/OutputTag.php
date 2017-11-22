<?php

namespace Ease\Html;

/**
 * HTML5 output tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class OutputTag extends PairTag
{

    /**
     * Defines the result of a calculation
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('output', $properties, $content);
    }
}
