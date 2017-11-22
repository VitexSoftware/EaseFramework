<?php

namespace Ease\Html;

/**
 * HTML em tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class EmTag extends PairTag
{

    /**
     * Tag kurzívu.
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('em', $properties, $content);
    }
}
