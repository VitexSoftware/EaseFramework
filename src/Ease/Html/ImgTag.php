<?php

namespace Ease\Html;

/**
 * IMG tag class
 *
 * @subpackage 
 * @author     Vitex <vitex@hippy.cz>
 */
class ImgTag extends Tag
{

    /**
     * Html Obrazek
     *
     * @param string $image         url obrázku
     * @param string $hint          hint při nájezu myší
     * @param int    $width         šířka v pixelech
     * @param int    $height        výška v pixelech
     * @param array  $tagProperties ostatni nastaveni tagu
     */
    public function __construct($image, $hint = null, $width = null, $height = null, $tagProperties = null)
    {
        if (is_null($tagProperties)) {
            $tagProperties = array();
        }
        $tagProperties['src'] = $image;
        if (isset($hint)) {
            $tagProperties['title'] = $hint;
        }
        if (isset($width)) {
            $tagProperties['width'] = $width;
        }
        if (isset($height)) {
            $tagProperties['height'] = $height;
        }
        parent::__construct('img', $tagProperties);
    }

}
