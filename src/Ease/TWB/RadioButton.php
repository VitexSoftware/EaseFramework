<?php
/**
 * RadioButton Twitter Bootstrapu.
 */

namespace Ease\TWB;

class RadioButton extends \Ease\Html\DivTag
{

    /**
     *  RadioButton Twitter Bootstrapu.
     *
     * @param string     $name
     * @param string|int $value
     * @param mixed      $caption
     * @param array      $properties
     */
    public function __construct($name = null, $value = null, $caption = null,
                                $properties = [])
    {
        if (isset($properties['id'])) {
            $for = $properties['id'];
        } else {
            $for = $name;
        }
        parent::__construct(
            new \Ease\Html\LabelTag($for,
                [new \Ease\Html\InputRadioTag($name, $value, $properties), $caption]));
    }
}
