<?php

namespace Ease\Html;

/**
 * IMG tag class.
 *
 * @author     Vitex <vitex@hippy.cz>
 */
class ImgTag extends Tag
{

    /**
     * Html Obrazek.
     * Html Image.
     *
     * @param string $image         image URL
     * @param string $alt           alternat name for text only browsers
     * @param array  $tagProperties IMG tag properties
     */
    public function __construct($image, $alt = null, $tagProperties = [])
    {
        $tagProperties['src'] = $image;
        if (isset($alt)) {
            $tagProperties['alt'] = $alt;
        }
        parent::__construct('img', $tagProperties);
    }
}
