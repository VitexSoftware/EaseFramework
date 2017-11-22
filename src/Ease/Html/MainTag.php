<?php

namespace Ease\Html;

/**
 * HTML5 main tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class MainTag extends PairTag
{

    /**
     * Defines the main content of a document
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('main', $properties, $content);
    }
}
