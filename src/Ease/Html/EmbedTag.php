<?php

namespace Ease\Html;

/**
 * HTML5 embed tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EmbedTag extends PairTag
{

    /**
     * Defines a container for an external (non-HTML) application
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('embed', $properties, $content);
    }
}
