<?php

namespace Ease\Html;

/**
 * Preformátovaný text
 */
class PreTag extends Ease\Html\PairTag
{

    /**
     * Preformátovaný text
     *
     * @param string|mixed $content
     * @param array        $tagProperties
     */
    public function __construct($content = null, $tagProperties = null)
    {
        parent::__construct('pre', $tagProperties, $content);
    }

}
