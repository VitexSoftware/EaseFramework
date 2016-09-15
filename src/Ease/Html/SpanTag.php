<?php

namespace Ease\Html;

/**
 * HTML span tag.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class SpanTag extends PairTag
{

    /**
     * <span> tag.
     *
     * @deprecated since version 1.0
     *
     * @param string $name       jméno a ID tagu
     * @param mixed  $content    vkládaný obsah
     * @param array  $properties parametry tagu
     */
    public function __construct($name, $content = null, $properties = null)
    {
        if ($name) {
            $this->setTagName($name);
        }
        parent::__construct('span', $properties, $content);
    }
}
