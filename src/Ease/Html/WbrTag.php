<?php

namespace Ease\Html;

/**
 * HTML5 wbr tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class WbrTag extends PairTag
{

    /**
     * Defines a possible line-break
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('wbr', $properties, $content);
    }
}
