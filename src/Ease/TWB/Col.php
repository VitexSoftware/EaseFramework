<?php

namespace Ease\TWB;

class Col extends \Ease\Html\Div
{

    /**
     * Bunka CSS tabulky bootstrapu.
     *
     * @link  http://getbootstrap.com/css/#grid
     *
     * @param int    $size       Velikost políčka 1 - 12
     * @param mixed  $content    Obsah políčka
     * @param string $target     Typ zařízení xs|sm|md|lg
     * @param array  $properties Další vlastnosti tagu
     */
    public function __construct($size, $content = null, $target = 'md',
                                $properties = null)
    {
        if (is_null($properties)) {
            $properties = [];
        }
        $properties['class'] = 'col-'.$target.'-'.$size;
        parent::__construct($content, $properties);
    }
}
