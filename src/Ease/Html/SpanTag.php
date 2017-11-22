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
     * @param mixed $content    content entered
     * @param array $properties tag parameters
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('span', $properties, $content);
    }
}
