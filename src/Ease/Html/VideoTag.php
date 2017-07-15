<?php

namespace Ease\Html;

/**
 * HTML5 video tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class VideoTag extends PairTag
{

    /**
     * Defines video or movie
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('video', $properties, $content);
    }
}
