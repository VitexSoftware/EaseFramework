<?php

namespace Ease\Html;

/**
 * Horizontal line tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class HrTag extends Ease\Html\Tag
{

    /**
     * Horizontal line tag
     *
     * @param array $properties parametry tagu
     */
    public function __construct($properties = null)
    {
        parent::__construct('hr', $properties);
    }

}
