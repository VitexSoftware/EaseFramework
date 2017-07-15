<?php

namespace Ease\Html;

/**
 * HTML5 track tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class TrackTag extends PairTag
{

    /**
     * Defines text tracks for media elements (<video> and <audio>)
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('track', $properties, $content);
    }
}
