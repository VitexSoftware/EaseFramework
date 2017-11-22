<?php

namespace Ease\Html;

/**
 * HTML Table row class.
 *
 * @author     Vitex <vitex@hippy.cz>
 */
class TrTag extends PairTag
{

    /**
     * TR tag.
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('tr', $properties, $content);
    }
}
