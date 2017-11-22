<?php

namespace Ease\Html;

/**
 * HTML H3 tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class H3Tag extends PairTag
{

    /**
     * Simple H3 tag.
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('h3', $properties, $content);
    }
}
