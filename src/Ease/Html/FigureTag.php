<?php

namespace Ease\Html;

/**
 * HTML5 figure tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class FigureTag extends PairTag
{

    /**
     * Defines self-contained content
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('figure', $properties, $content);
    }
}
