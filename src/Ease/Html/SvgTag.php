<?php

namespace Ease\Html;

/**
 * HTML5 svg tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class SvgTag extends PairTag
{

    /**
     * Draw scalable vector graphics
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('svg', $properties, $content);
    }
}
