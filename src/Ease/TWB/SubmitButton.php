<?php

namespace Ease\TWB;

/**
 * Odesílací tlačítko formuláře Twitter Bootstrapu.
 */
class SubmitButton extends \Ease\Html\ButtonTag
{

    /**
     * Odesílací tlačítko formuláře Twitter Bootstrapu.
     *
     * @param string $value vracená hodnota
     * @param string $type  primary|info|success|warning|danger|inverse|link
     */
    public function __construct($value = null, $type = null, $properties = [])
    {
        if (is_null($type)) {
            $properties['class'] = 'btn';
        } else {
            $properties['class'] = 'btn btn-'.$type;
        }
        parent::__construct($value, $properties);
        Part::twBootstrapize();
    }
}
