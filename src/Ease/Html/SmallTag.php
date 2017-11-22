<?php

namespace Ease\Html;

/**
 * HTML major heading tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class SmallTag extends PairTag
{

    /**
     * Tag pro male písmo.
     * Small font tag
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('small', $properties, $content);
    }
}
