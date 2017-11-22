<?php

namespace Ease\Html;

/**
 * HTML list item tag class.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class LiTag extends PairTag
{

    /**
     * Simple LI tag.
     *
     * @param mixed $ulContents obsah položky seznamu
     * @param array $properties parametry LI tagu
     */
    public function __construct($ulContents = null, $properties = [])
    {
        parent::__construct('li', $properties, $ulContents);
    }
}
