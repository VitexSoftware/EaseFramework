<?php

/**
 * RadioButton Twitter Bootstrapu
 */

namespace Ease\TWB;

class RadioButton extends Ease\Html\DivTag
{

    /**
     *  RadioButton Twitter Bootstrapu
     *
     * @param string     $name
     * @param string|int $value
     * @param mixed      $caption
     * @param array      $properties
     */
    function __construct($name = null, $value = null, $caption = null, $properties = null)
    {
        if (isset($properties['id'])) {
            $for = $properties['id'];
        } else {
            $for = $name;
        }
        parent::__construct(null, new Ease\Html\LabelTag($for, array(new Ease\Html\InputRadioTag($name, $value, $properties), $caption)));
    }

}
