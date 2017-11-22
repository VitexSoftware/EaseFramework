<?php

namespace Ease\Html;

/**
 * HTML5 summary tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class SummaryTag extends PairTag
{

    /**
     * Defines a visible heading for a <details> element
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('summary', $properties, $content);
    }
}
