<?php

namespace Ease\Html;

/**
 *  fragment skriptu ve stránce.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class JavaScript extends ScriptTag
{

    /**
     * fragment javaskriptu ve stránce.
     *
     * @param string $content text scriptu
     */
    public function __construct($content, $properties = [])
    {
        if (is_null($properties)) {
            $properties = ['type' => 'text/javascript'];
        } else {
            $properties['type'] = 'text/javascript';
        }
        parent::__construct($content, $properties);
    }
}
