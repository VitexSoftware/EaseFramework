<?php

namespace Ease\TWB;

class Col extends Ease\Html\DivTag
{

    /**
     * Bunka CSS tabulky bootstrapu
     *
     * @link http://getbootstrap.com/css/#grid
     * @param int    $size       Velikost políčka 1 - 12
     * @param mixed  $content    Obsah políčka
     * @param string $target     Typ zařízení xs|sm|md|lg
     * @param array  $properties Další vlastnosti tagu
     */
    function __construct($size, $content = null, $target = 'md', $properties = null)
    {
        if (is_null($properties)) {
            $properties = array();
        }
        $properties['class'] = 'col-' . $target . '-' . $size;
        parent::__construct(null, $content, $properties);
    }

}
