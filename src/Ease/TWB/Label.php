<?php

/**
 * Návěstí bootstrapu
 */

namespace Ease\TWB;

class Label extends \Ease\Html\SpanTag
{

    /**
     * Návěstí bootstrapu
     *
     * @link http://getbootstrap.com/components/#labels
     *
     * @param string $type       info|warning|error|success
     * @param mixed  $content
     * @param array  $properties
     */
    function __construct($type = 'default', $content = null, $properties = null)
    {
        if (isset($properties['class'])) {
            $properties['class'] .= ' label label-' . $type;
        } else {
            $properties['class'] = ' label label-' . $type;
        }
        parent::__construct(null, $content, $properties);
    }

}
