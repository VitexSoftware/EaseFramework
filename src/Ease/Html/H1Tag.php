<?php

namespace Ease\Html;

/**
 * HTML major heading tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class H1Tag extends PairTag
{

    /**
     * Simple H1 Tag.
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('h1', $properties, $content);
    }
}
