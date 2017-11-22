<?php

namespace Ease\Html;

/**
 * HTML5 section tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class SectionTag extends PairTag
{

    /**
     * Defines a section in a document
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('section', $properties, $content);
    }
}
