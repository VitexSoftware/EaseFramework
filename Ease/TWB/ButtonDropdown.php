<?php

namespace Ease\TWB;

class ButtonDropdown extends Ease\Html\Div
{

    /**
     * Rozbalovací nabídka
     * @var Ease\Html\UlTag
     */
    public $dropdown = null;

    /**
     *
     * @var type
     */
    public $button = null;

    /**
     * Tlačítko s rozbalovacím menu
     *
     * @param string $label      popisek tlačítka
     * @param string $type       primary|info|success|warning|danger|inverse|link
     * @param string $size       lg = velký, sm = menší, xs = nejmenší
     * @param array  $items      položky menu
     * @param array  $properties Parametry tagu
     */
    function __construct($label = null, $type = 'default', $size = null, $items = null, $properties = null)
    {
        parent::__construct(null, $properties);
        $this->setTagClass('btn-group');
        $btnClass = 'btn btn-' . $type . ' ';
        if ($size) {
            $btnClass .= 'btn-' . $size;
        }
        $this->button = $this->addItem(new Ease\Html\ButtonTag(array($label . ' <span class="caret"></span>'), array('class' => $btnClass . ' dropdown-toggle', 'type' => 'button', 'data-toggle' => 'dropdown')));
        $this->dropdown = $this->addItem(new Ease\Html\UlTag(null, array('class' => 'dropdown-menu', 'role' => 'menu')));
        if (count($items)) {
            foreach ($items as $item) {
                $this->addMenuItem($item);
            }
        }
    }

    /**
     * Vloží položku do menu tlačítka
     *
     * @param type $pageItem
     * @return Ease\Html\LiTag
     */
    function addMenuItem($pageItem)
    {
        return $this->dropdown->addItemSmart($pageItem);
    }

    /**
     * delící čára menu
     *
     * @return \Ease\Html\LiTag
     */
    static function divider()
    {
        return new Ease\Html\LiTag(null, array('class' => 'divider'));
    }

}
