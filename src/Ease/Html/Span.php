<?php

namespace Ease\Html;

/**
 * HTML span tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class Span extends PairTag
{

    /**
     * <span> tag.
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('span', $properties, $content);
    }
}
