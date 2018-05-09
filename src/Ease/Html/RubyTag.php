<?php

namespace Ease\Html;

/**
 * HTML5 ruby tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class RubyTag extends PairTag
{

    /**
     * Defines a ruby annotation (for East Asian typography)
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('ruby', $properties, $content);
    }
}
