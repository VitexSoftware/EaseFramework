<?php

namespace Ease\TWB;

/**
 * Textarea pro Twitter Bootstap.
 *
 * @author vitex
 */
class Textarea extends \Ease\Html\TextareaTag
{
    /**
     * Textarea.
     *
     * @param string $name       jméno tagu
     * @param string $content    obsah textarey
     * @param array  $properties vlastnosti tagu
     */
    public function __construct($name, $content = '', $properties = [])
    {
        parent::__construct($name, $content, $properties);
        $this->addTagClass('form-control');
    }
}
