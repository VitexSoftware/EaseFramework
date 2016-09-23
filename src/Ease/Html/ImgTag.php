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
     * @param string $image        image URL
     * @param string $alt          alternat name for text only browsers
     * @param array  $tagProperties ostatni nastaveni tagu
     */
    public function __construct($image, $alt = null, $tagProperties = null)
    {
        if (is_null($tagProperties)) {
            $tagProperties = [];
        }
        $tagProperties['src'] = $image;
        if (isset($alt)) {
            $tagProperties['alt'] = $alt;
        }
        parent::__construct('img', $tagProperties);
    }
}
