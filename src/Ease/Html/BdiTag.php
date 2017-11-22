<?php

namespace Ease\Html;

/**
 * HTML5 BDI tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class BdiTag extends PairTag
{

    /**
     * Isolates a part of text that might be formatted in a different direction from other text outside it
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('bdi', $properties, $content);
    }
}
