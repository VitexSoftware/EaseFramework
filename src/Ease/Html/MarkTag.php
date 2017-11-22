<?php

namespace Ease\Html;

/**
 * HTML5 mark tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class MarkTag extends PairTag
{

    /**
     * Defines marked/highlighted text
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('mark', $properties, $content);
    }
}
