<?php

namespace Ease\TWB;

class RadioButtonGroup extends \Ease\Container
{
    /**
     * Jméno.
     *
     * @var string
     */
    public $name = null;

    /**
     * Typ.
     *
     * @var bool
     */
    public $inline = false;

    /**
     * Položky k zobrazení.
     *
     * @var array
     */
    public $radios = [];

    /**
     * Předvolená hodnota.
     *
     * @var string
     */
    public $checked = null;

    /**
     * Zobrazí pole radiobuttonů.
     *
     * @param string $name
     * @param array  $radios  pole Hodnota=>Popisek
     * @param string $checked
     * @param bool   $inline
     */
    public function __construct($name, $radios, $checked = null, $inline = false)
    {
        $this->name    = $name;
        $this->checked = $checked;
        $this->inline  = $inline;
        $this->radios  = $radios;
        parent::__construct();
    }

    /**
     * Seskládá pole radiobuttonů.
     */
    public function finalize()
    {
        $class = 'radio';
        if ($this->inline) {
            $class .= '-inline';
        }
        $pos = 1;
        foreach ($this->radios as $value => $caption) {
            if ($value == $this->checked) {
                $checked = 'checked';
            } else {
                $checked = null;
            }
            $tagProperties = ['id' => $this->name.$pos++, 'name' => $this->name,
                $checked,];
            $this->addItem(new \Ease\Html\DivTag(new \Ease\Html\LabelTag(null,
                        [new \Ease\Html\InputRadioTag($this->name, $value,
                            $tagProperties), $caption]), ['class' => $class]));
        }
    }
}
