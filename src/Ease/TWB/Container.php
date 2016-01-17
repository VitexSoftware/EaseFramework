<?php

/**
 * Twitter Bootrstap Container
 */

namespace Ease\TWB;

class Container extends \Ease\Html\Div
{

    /**
     * Twitter Bootrstap Container
     *
     * @param mixed $content
     */
    public function __construct($content = null)
    {
        parent::__construct($content, array('class' => 'container'));
    }

}
