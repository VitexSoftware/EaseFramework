<?php

namespace Ease\Html;

/**
 * HTML5 figcaption tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class FigCaptionTag extends PairTag
{

    /**
     * Defines a caption for a <figure> element
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('figcaption', $properties, $content);
    }
}
