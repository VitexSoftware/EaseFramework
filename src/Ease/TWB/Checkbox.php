<?php
/**
 * Checkbox pro TwitterBootstrap.
 */

namespace Ease\TWB;

class Checkbox extends \Ease\Html\DivTag
{
    /**
     * Odkaz na checkbox.
     *
     * @var \Ease\Html\CheckboxTag
     */
    public $checkbox = null;

    /**
     * Checkbox pro TwitterBootstrap.
     *
     * @param string     $name
     * @param string|int $value
     * @param mixed      $content
     * @param bool       $checked
     * @param array      $properties
     */
    public function __construct($name = null, $value = 'on', $content = null,
                                $checked = false, $properties = [])
    {
        $label          = new \Ease\Html\LabelTag($name);
        $this->checkbox = $label->addItem(new \Ease\Html\CheckboxTag($name,
                $checked, $value, $properties));
        if ($content) {
            $label->addItem($content);
        }
        parent::__construct($label);
    }
}
