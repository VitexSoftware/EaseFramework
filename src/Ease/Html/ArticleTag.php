<?php

namespace Ease\Html;

/**
 * HTML5 Article tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class ArticleTag extends PairTag
{

    /**
     * Defines an article in a document
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('article', $properties, $content);
    }
}
