<?php

namespace Ease\Html;

/**
 * HTML5 keygen tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class KeygenTag extends PairTag
{

    /**
     * Defines a key-pair generator field (for forms)
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('keygen', $properties, $content);
    }
}
