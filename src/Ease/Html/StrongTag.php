<?php

namespace Ease\Html;

/**
 * HTML major heading tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class StrongTag extends PairTag
{

    /**
     * Tag pro tučné písmo.
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('strong', $properties, $content);
    }
}
