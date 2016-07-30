<?php
/**
 * Twitter Bootrstap Row.
 */

namespace Ease\TWB;

class Row extends \Ease\Html\Div
{
    /**
     * Twitter Bootrstap Row.
     *
     * @param mixed $content Prvotní obsah
     */
    public function __construct($content = null)
    {
        parent::__construct(null, $content, ['class' => 'row']);
    }

    /**
     * Vloží do řádku políčko.
     *
     * @link   http://getbootstrap.com/css/#grid
     *
     * @param int    $size       Velikost políčka 1 - 12
     * @param mixed  $content    Obsah políčka
     * @param string $target     Typ zařízení xs|sm|md|lg
     * @param array  $properties Další vlastnosti tagu
     *
     * @return Col Column contains $content
     */
    public function &addColumn($size, $content = null, $target = 'md',
                               $properties = null)
    {
        $added = $this->addItem(new Col($size, $content, $target, $properties));

        return $added;
    }
}
