<?php

namespace Ease\TWB;

/**
 * Textarea pro Twitter Bootstap
 *
 * @author vitex
 */
class Textarea extends Ease\Html\TextareaTag
{

    /**
     * Textarea
     *
     * @param string $name       jméno tagu
     * @param string $content    obsah textarey
     * @param array  $properties vlastnosti tagu
     */
    public function __construct($name, $content = '', $properties = null)
    {
        if (is_null($properties) || !isset($properties['class'])) {
            $properties = array('class' => 'form-control');
        } else {
            $properties['class'] .= ' form - control  ';
        }
        parent::__construct($name, $content, $properties);
    }

}
