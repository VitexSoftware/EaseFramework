<?php

namespace Ease\Html;

/**
 * HTML5 meter tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class MeterTag extends PairTag
{

    /**
     * Defines a scalar measurement within a known range (a gauge)
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('meter', $properties, $content);
    }
}
