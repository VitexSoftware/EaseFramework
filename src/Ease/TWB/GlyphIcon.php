<?php

namespace Ease\TWB;

/**
 * @deprecated since version 1.0
 */
class GlyphIcon extends \Ease\Html\Span
{

    /**
     * Vloží ikonu.
     *
     * @link  http://getbootstrap.com/components/#glyphicons Přehled ikon
     *
     * @param string $code Kód ikony z přehledu
     */
    public function __construct($code)
    {
        parent::__construct(null, ['class' => 'glyphicon glyphicon-'.$code]);
    }
}
