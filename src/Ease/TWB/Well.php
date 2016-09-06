<?php

namespace Ease\TWB;

/**
 * Twitter Bootrstap Well.
 */
class Well extends \Ease\Html\Div
{

    /**
     * Twitter Bootrstap Well.
     *
     * @param mixed $content
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct($content, $properties);
        $this->addTagClass('well');
    }
}