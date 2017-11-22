<?php

namespace Ease\Html;

/**
 * Horizontal line tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class HrTag extends Tag
{

    /**
     * Horizontal line tag.
     *
     * @param array $properties parametry tagu
     */
    public function __construct($properties = [])
    {
        parent::__construct('hr', $properties);
    }
}
