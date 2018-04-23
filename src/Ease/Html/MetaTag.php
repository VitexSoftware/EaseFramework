<?php

namespace Ease\Html;

/**
 * HTML main tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class MetaTag extends PairTag
{

    /**
     * Defines the main content of a document
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('meta', $properties, $content);
    }
}
