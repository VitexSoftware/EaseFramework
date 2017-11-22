<?php

namespace Ease\Html;

/**
 * HTML5 footer tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class FooterTag extends PairTag
{

    /**
     * Defines a footer for a document or section
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('footer', $properties, $content);
    }
}
