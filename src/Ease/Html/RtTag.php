<?php

namespace Ease\Html;

/**
 * HTML5 rt tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class RtTag extends PairTag
{

    /**
     * Defines an explanation/pronunciation of characters (for East Asian typography)
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('rt', $properties, $content);
    }
}
