<?php

namespace Ease\Html;

/**
 * HTML Div tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class Div extends PairTag
{

    /**
     * Prostý tag odstavce DIV
     *
     * @param string $name       ID tagu
     * @param mixed  $content    vložené prvky
     * @param array  $properties pole parametrů
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('div', $properties, $content);
    }
}
