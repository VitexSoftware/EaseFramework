<?php

namespace Ease\Html;

/**
 * HTML Paragraph class tag.
 *
 * @author     Vitex <vitex@hippy.cz>
 */
class PTag extends PairTag
{

    /**
     * Odstavec.
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('p', $properties, $content);
    }
}
