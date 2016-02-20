<?php

namespace Ease\Html;

/**
 * Obecný párový HTML tag
 *
 * @subpackage 
 * @author     Vitex <vitex@hippy.cz>
 */
class PairTag extends Tag {

    /**
     * Character to close tag
     * @var type
     */
    public $trail = '';

    /**
     * Render tag and its contents
     */
    public function draw() {
        $this->tagBegin();
        $this->drawAllContents();
        $this->tagEnclousure();
    }

    /**
     * Zobrazí počátek párového tagu
     */
    public function tagBegin() {
        parent::draw();
    }

    /**
     * Zobrazí konec párového tagu
     */
    public function tagEnclousure() {
        echo '</' . $this->tagType . '>';
    }

}
