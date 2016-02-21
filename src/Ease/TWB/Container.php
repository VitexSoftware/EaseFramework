<?php

/**
 * Twitter Bootrstap Container
 *
 * @package   EaseFrameWork
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2016 Vitex@hippy.cz (G)
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
        parent::__construct($content, ['class' => 'container']);
    }

}
