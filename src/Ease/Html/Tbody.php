<?php

namespace Ease\Html;

class Tbody extends PairTag
{
    /**
     * <tbody>.
     *
     * @param mixed $content
     * @param array $properties
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct('tbody', $properties, $content);
    }
}
