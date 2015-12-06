<?php

namespace Ease\Html;

/**
 * Obsah definice
 */
class DdTag extends Ease\Html\PairTag
{

    /**
     * Obsah definice
     *
     * @param string|mixed $content
     * @param array        $tagProperties
     */
    public function __construct($content = null, $tagProperties = null)
    {
        parent::__construct('dd', $tagProperties, $content);
    }

}
