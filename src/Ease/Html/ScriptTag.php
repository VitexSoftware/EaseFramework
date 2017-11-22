<?php

namespace Ease\Html;

/**
 * Skript ve stránce.
 */
class ScriptTag extends PairTag
{

    /**
     * Skript.
     *
     * @param string|mixed $content
     * @param array        $tagProperties
     */
    public function __construct($content = null, $tagProperties = null)
    {
        parent::__construct(
            'script', $tagProperties, '// <![CDATA[
'.$content.'
// ]]>'
        );
    }
}
