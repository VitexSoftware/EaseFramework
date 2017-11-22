<?php

namespace Ease\Html;

/**
 * HTML5 datalist tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class DatalistTag extends PairTag
{

    /**
     * Specifies a list of pre-defined options for input controls
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('datalist', $properties, $content);
    }
}
