<?php

namespace Ease\Html;

/**
 * HTML5 Aside tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class AsideTag extends PairTag
{

    /**
     * Defines content aside from the page content
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('aside', $properties, $content);
    }
}
