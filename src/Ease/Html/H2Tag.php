<?php

namespace Ease\Html;

/**
 * HTML H2 tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class H2Tag extends PairTag
{

    /**
     * Nadpis druhé velikosti.
     *
     * @param mixed  $content    text nadpisu
     * @param string $properties parametry tagu
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('h2', $properties, $content);
    }
}
