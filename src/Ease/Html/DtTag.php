<?php

namespace Ease\Html;

/**
 * Pojem definice.
 */
class DtTag extends PairTag
{

    /**
     * Pojem definice.
     *
     * @param string|mixed $content       název pojmu / klíčové slovo
     * @param array        $tagProperties
     */
    public function __construct($content = null, $tagProperties = null)
    {
        parent::__construct('dt', $tagProperties, $content);
    }
}
