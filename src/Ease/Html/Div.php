<?php

namespace Ease\Html;

/**
 * HTML Div tag.
 *
 * @deprecated since 1.2.3
 * @author Vitex <vitex@hippy.cz>
 */
class Div extends PairTag
{

    /**
     * Prostý tag odstavce DIV.
     *
     * @param mixed  $content    vložené prvky
     * @param array  $properties pole parametrů
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('div', $properties, $content);
    }
}
