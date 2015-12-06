<?php

/**
 * Twitter Bootrstap Container
 */

namespace Ease\TWB;

class Container extends Ease\Html\DivTag
{

    /**
     * Twitter Bootrstap Container
     *
     * @param mixed $content
     */
    public function __construct($content = null)
    {
        parent::__construct(null, $content, array('class' => 'container'));
    }

}
