<?php

namespace Ease\Html;

/**
 * HTML top tag class.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class HtmlTag extends PairTag
{
    public $langCode = 'cs-CZ';

    /**
     * HTML.
     *
     * @param mixed $content vložený obsah - tělo stránky
     */
    public function __construct($content = null)
    {
        parent::__construct('html', ['lang' => $this->langCode], $content);
    }

    /**
     * Nastaví jméno objektu na "html".
     *
     * @param string $ObjectName jméno objektu
     */
    public function setObjectName($ObjectName = null)
    {
        parent::setObjectName('html');
    }
}
