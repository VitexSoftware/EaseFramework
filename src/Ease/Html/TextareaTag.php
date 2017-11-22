<?php

namespace Ease\Html;

/**
 * Textové pole.
 *
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class TextareaTag extends PairTag
{
    /**
     * Odkaz na obsah.
     */
    public $content = null;
    public $setName = true;

    /**
     * Textarea.
     *
     * @param string $name       jméno tagu
     * @param string $content    obsah textarey
     * @param array  $properties vlastnosti tagu
     */
    public function __construct($name, $content = '', $properties = [])
    {
        $this->setTagName($name);
        parent::__construct('textarea', $properties);
        if ($content) {
            $this->addItem($content);
        }
    }

    /**
     * Nastaví obsah.
     *
     * @param string $value hodnota
     */
    public function setValue($value)
    {
        $this->pageParts = [];
        $this->addItem($value);
    }
}
