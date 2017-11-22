<?php

namespace Ease\Html;

/**
 * HTML5 progress tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class ProgressTag extends PairTag
{

    /**
     * Represents the progress of a task
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('progress', $properties, $content);
    }
}
