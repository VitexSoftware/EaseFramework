<?php

namespace Ease\Html;

/**
 * HTML5 source tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class SourceTag extends PairTag
{

    /**
     * Defines multiple media resources for media elements (<video> and <audio>)
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('source', $properties, $content);
    }
}
