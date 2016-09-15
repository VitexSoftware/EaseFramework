<?php

namespace Ease\Html;

/**
 * Odeslání formuláře obrázkem.
 *
 * @deprecated since version 1.0
 *
 * @author     Vítězslav Dvořák <vitex@hippy.cz>
 */
class ImageSubbmit extends InputTag
{

    /**
     * Zobrazí <input type="image">.
     *
     * @param string $image url obrázku
     * @param string $label popisek obrázku
     * @param string $value vracená hodnota
     * @param string $hint  text tipu
     */
    public function __construct($image, $label, $value = null, $hint = null)
    {
        $Properties = ['type' => 'image'];
        if (!$value) {
            $value = trim(str_replace([' ', '?'], '',
                    @iconv('utf-8', 'us-ascii//TRANSLIT', strtolower($label))));
        } else {
            $Properties['value'] = $value;
        }
        if ($hint) {
            $Properties['title'] = $hint;
        }
        $Properties['src'] = $image;
        $this->setTagProperties($Properties);
        parent::__construct($value, $label);
    }
}
