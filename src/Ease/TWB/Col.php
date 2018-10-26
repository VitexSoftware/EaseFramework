<?php

namespace Ease\TWB;

class Col extends \Ease\Html\DivTag
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
                                $properties = [])
    {
        if (array_key_exists('class', $properties)) {
            $addClass = $properties['class'];
        } else {
            $addClass = '';
        }
        $properties['class'] = 'col-'.$target.'-'.$size;
        parent::__construct($content, $properties);
        if (strlen($addClass)) {
            $this->addTagClass($addClass);
        }
    }
}
