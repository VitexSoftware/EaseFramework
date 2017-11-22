<?php

namespace Ease\Html;

/**
 * HTML title class.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class TitleTag extends PairTag
{

    /**
     * Title html tag.
     *
     * @param string $contents   text titulku
     * @param array  $properties parametry tagu
     */
    public function __construct($contents = null, $properties = [])
    {
        parent::__construct('title', $properties, $contents);
    }
}
