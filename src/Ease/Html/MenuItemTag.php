<?php

namespace Ease\Html;

/**
 * HTML5 menuitem tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class MenuItemTag extends PairTag
{

    /**
     * Defines a command/menu item that the user can invoke from a popup menu
     *
     * @param mixed  $content    items included
     * @param array  $properties params array
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('menuitem', $properties, $content);
    }
}
