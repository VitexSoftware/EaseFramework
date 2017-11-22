<?php

namespace Ease\Html;

/**
 * HTML5 Details tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class DetailsTag extends PairTag
{

    /**
     * Defines additional details that the user can view or hide
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('details', $properties, $content);
    }
}
