<?php
/**
 * Návěstí bootstrapu.
 */

namespace Ease\TWB;

class Label extends \Ease\Html\Span
{

    /**
     * Návěstí bootstrapu.
     *
     * @link http://getbootstrap.com/components/#labels
     *
     * @param string $type       info|warning|error|success
     * @param mixed  $content
     * @param array  $properties
     */
    public function __construct($type = 'default', $content = null,
                                $properties = [])
    {
        if (isset($properties['class'])) {
            $properties['class'] .= ' label label-'.$type;
        } else {
            $properties['class'] = ' label label-'.$type;
        }
        parent::__construct($content, $properties);
    }
}
