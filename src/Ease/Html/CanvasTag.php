<?php

namespace Ease\Html;

/**
 * HTML5 canvas tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class CanvasTag extends PairTag
{

    /**
     * Draw graphics, on the fly, via scripting (usually JavaScript)
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('canvas', $properties, $content);
    }
}
