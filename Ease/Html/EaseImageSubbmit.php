<?php

namespace Ease\Html;

/**
 * Odeslání formuláře obrázkem
 *
 * @deprecated since version 1.0
 * @author Vítězslav Dvořák <vitex@hippy.cz>
 */
class ImageSubbmit extends Ease\Html\InputTag
{

    /**
     * Zobrazí <input type="image">
     *
     * @param string $Image url obrázku
     * @param string $Label popisek obrázku
     * @param string $value vracená hodnota
     * @param string $Hint  text tipu
     */
    public function __construct($Image, $Label, $value = null, $Hint = null)
    {
        $Properties = array('type' => 'image');
        if (!$value) {
            $value = trim(str_replace(array(' ', '?'), '', @iconv('utf-8', 'us-ascii//TRANSLIT', strtolower($Label))));
        } else {
            $Properties['value'] = $value;
        }
        if ($Hint) {
            $Properties['title'] = $Hint;
        }
        $Properties['src'] = $Image;
        $this->setTagProperties($Properties);
        parent::__construct($value, $Label);
    }

}
