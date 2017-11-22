<?php

namespace Ease\Html;

/**
 * HTML Table Header cell class.
 *
 * @author     Vitex <vitex@hippy.cz>
 */
class ThTag extends PairTag
{

    /**
     * Buňka s popiskem tabulky.
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('th', $properties, $content);
    }
}
