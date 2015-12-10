<?php

namespace Ease\TWB;

/**
 * Twitter Bootrstap Well
 */
class Well extends \Ease\Html\DivTag
{

    /**
     * Twitter Bootrstap Well
     *
     * @param mixed $content
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct(null, $content, $properties);
        $this->addTagClass('well');
    }

}
