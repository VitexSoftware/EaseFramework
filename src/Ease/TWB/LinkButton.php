<?php

namespace Ease\TWB;

/**
 * Odkazové tlačítko twbootstrabu.
 *
 * @author    Vítězslav Dvořák <vitex@hippy.cz>
 * @copyright 2012-2017 Vitex@vitexsoftware.cz (G)
 *
 * @link      http://twitter.github.com/bootstrap/base-css.html#buttons Buttons
 */
class LinkButton extends \Ease\Html\ATag
{

    /**
     * Odkazové tlačítko twbootstrabu.
     *
     * @param string $href       cíl odkazu
     * @param mixed  $contents   obsah tlačítka
     * @param string $type       primary|info|success|warning|danger|inverse|link
     * @param array  $properties dodatečné vlastnosti
     */
    public function __construct($href, $contents = null, $type = null,
                                $properties = [])
    {
        if (isset($properties['class'])) {
            $class = ' '.$properties['class'];
        } else {
            $class = '';
        }
        if (is_null($type)) {
            $properties['class'] = 'btn btn-default';
        } else {
            $properties['class'] = 'btn btn-'.$type;
        }
        $properties['class'] .= $class;
        parent::__construct($href, $contents, $properties);
        Part::twBootstrapize();
    }
}
