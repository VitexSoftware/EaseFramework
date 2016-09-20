<?php

namespace Ease\Html;

/**
 * HTML top tag class.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class HtmlTag extends PairTag
{
    public $LangCode = 'cs-CZ';

    /**
     * HTML.
     *
     * @param mixed $Content vložený obsah - tělo stránky
     */
    public function __construct($Content = null)
    {
        parent::__construct('html',
            ['lang' => $this->langCode, 'xmlns' => 'http://www.w3.org/1999/xhtml',
            'xml:lang' => $this->langCode, ], $Content);
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
