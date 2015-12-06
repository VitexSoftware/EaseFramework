<?php

namespace Ease\Html;

/**
 * Siple HTML head tag class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class SimpleHeadTag extends Ease\Html\PairTag
{

    /**
     * Content type of webpage
     * @var string
     */
    public static $ContentType = 'text/html';

    /**
     * head tag with defined meta http-equiv content type
     *
     * @param mixed $Contents   vkládaný obsah
     * @param array $Properties parametry tagu
     */
    public function __construct($Contents = null, $Properties = null)
    {
        parent::__construct('head', $Properties, $Contents);
        $this->addItem('<meta http-equiv="Content-Type" content="' . self::$ContentType . '; charset=' . $this->charSet . '" />');
    }

}
