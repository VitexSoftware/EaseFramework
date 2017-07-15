<?php

namespace Ease\Html;

/**
 * HTML5 Article tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class AudioTag extends PairTag
{

    /**
     * Defines sound content
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('audio', $properties, $content);
    }
}
