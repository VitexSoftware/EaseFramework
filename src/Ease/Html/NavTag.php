<?php

namespace Ease\Html;

/**
 * HTML5 Nav tag
 *
 * @author Vitex <vitex@hippy.cz>
 */
class NavTag extends PairTag {

    /**
     * Tag semantiky navigaze
     *
     * @param mixed $content    vložené prvky
     * @param array $properties pole parametrů
     */
    public function __construct($content = null, $properties = null) {
        parent::__construct('div', $properties, $content);
    }

}
