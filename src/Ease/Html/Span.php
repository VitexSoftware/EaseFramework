<?php

namespace Ease\Html;

/**
 * HTML span tag.
 *
 * @deprecated since version 1.4.1
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
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('span', $properties, $content);
    }
}
