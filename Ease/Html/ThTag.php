<?php

namespace Ease\Html;

/**
 * HTML Table Header cell class
 *
 * @subpackage Ease\Html\
 * @author     Vitex <vitex@hippy.cz>
 */
class ThTag extends Ease\Html\PairTag
{

    /**
     * Buňka s popiskem tabulky
     *
     * @param mixed $content    vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('th', $properties, $content);
    }

}
