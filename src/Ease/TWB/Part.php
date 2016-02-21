<?php

/**
 * Twitter Bootstrap common class
 *
 * @author Vitex <vitex@hippy.cz>
 */

namespace Ease\TWB;

class Part extends \Ease\JQuery\Part
{

    /**
     * Vložení náležitostí pro twitter bootstrap
     */
    public function __construct()
    {
        parent::__construct();
        self::twBootstrapize();
    }

    /**
     * Opatří objekt vším potřebným pro funkci bootstrapu
     */
    public static function twBootstrapize()
    {
        parent::jQueryze();
        $webPage = \Ease\Shared::webPage();
        $webPage->includeJavaScript('twitter-bootstrap/js/bootstrap.js', 1, true);
        if (isset($webPage->mainStyle)) {
            $webPage->includeCss($webPage->mainStyle, true);
        }
        //TODO: ONCE: $webPage->head->addItem('<meta name="viewport"
        // content="width=device-width, initial-scale=1.0">');
        return true;
    }

    /**
     * Vrací ikonu
     *
     * @link  http://getbootstrap.com/components/#glyphicons Přehled ikon
     * @param string $code       Kód ikony z přehledu
     * @param array  $properties Vlastnosti Tagu
     */
    public static function GlyphIcon($code, $properties = null)
    {
        if (is_null($properties)) {
            $properties = ['class' => 'glyphicon glyphicon-' . $code];
        } else {
            if (isset($properties['class'])) {
                $properties['class'] = 'glyphicon glyphicon-' . $code . ' ' . $properties['class'];
            } else {
                $properties['class'] = 'glyphicon glyphicon-' . $code;
            }
        }
        return new \Ease\Html\Span(null, $properties);
    }

}
