<?php

namespace Ease\Html;

/**
 * HTML5 Dialog tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class DialogTag extends PairTag
{

    /**
     * Defines a dialog box or window
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('dialog', $properties, $content);
    }
}
