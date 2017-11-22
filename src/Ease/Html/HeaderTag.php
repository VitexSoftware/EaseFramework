<?php

namespace Ease\Html;

/**
 * HTML5 header tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class HeaderTag extends PairTag
{

    /**
     * Defines a header for a document or section
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('header', $properties, $content);
    }
}
