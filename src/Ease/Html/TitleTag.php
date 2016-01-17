<?php

namespace Ease\Html;

/**
 * HTML title class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class TitleTag extends PairTag
{

    /**
     * Title html tag
     *
     * @param string $Contents   text titulku
     * @param array  $Properties parametry tagu
     */
    public function __construct($Contents = null, $Properties = null)
    {
        parent::__construct('title', $Properties, $Contents);
    }

}
