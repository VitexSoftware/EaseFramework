<?php
/**
 * Twitter Bootrstap Container.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2009-2016 Vitex@hippy.cz (G)
 */

namespace Ease\TWB;

class Container extends \Ease\Html\DivTag
{

    /**
     * Twitter Bootrstap Container.
     *
     * @param mixed $content
     */
    public function __construct($content = null)
    {
        parent::__construct($content, ['class' => 'container']);
    }
}
