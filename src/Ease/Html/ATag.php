<?php

namespace Ease\Html;

/**
 * HTML hyperling class.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class ATag extends PairTag
{

    /**
     * zobrazí HTML odkaz.
     *
     * @param string $href       url odkazu
     * @param mixed  $contents   vkládaný obsah
     * @param array  $properties parametry tagu
     */
    public function __construct($href, $contents = null, $properties = [])
    {
        if (!is_array($properties)) {
            $properties = [];
        }
        if (!is_null($href)) {
            $properties['href'] = $href;
        }
        parent::__construct('a', $properties, $contents);
    }

}
