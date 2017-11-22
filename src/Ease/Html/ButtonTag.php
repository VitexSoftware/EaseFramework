<?php

namespace Ease\Html;

/**
 * Html element pro tlačítko.
 */
class ButtonTag extends PairTag
{

    /**
     * Html element pro tlačítko.
     *
     * @param string $content       obsah tlačítka
     * @param array  $tagProperties vlastnosti tagu
     */
    public function __construct($content, $tagProperties = null)
    {
        parent::__construct('button', $tagProperties, $content);
    }
}
