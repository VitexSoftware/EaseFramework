<?php

namespace Ease\Html;

/**
 * HtmlParam tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class ParamTag extends Ease\Html\Tag
{

    /**
     * Paramm tag
     *
     * @param string $name  jméno parametru
     * @param string $value hodnota parametru
     */
    public function __construct($name, $value)
    {
        parent::__construct('param', array('name' => $name, 'value' => $value));
    }

}
