<?php

namespace Ease\Html;

/**
 * HTML5 rp tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class RpTag extends PairTag
{

    /**
     * Defines what to show in browsers that do not support ruby annotations
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('rp', $properties, $content);
    }
}
