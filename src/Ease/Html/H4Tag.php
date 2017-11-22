<?php

namespace Ease\Html;

/**
 * HTML H4 tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class H4Tag extends PairTag
{

    /**
     * Simple H4 tag.
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('h4', $properties, $content);
    }
}
