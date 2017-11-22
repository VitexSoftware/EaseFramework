<?php

namespace Ease\Html;

class Thead extends PairTag
{

    /**
     * <thead>.
     *
     * @param mixed $content
     * @param array $properties
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct('thead', $properties, $content);
    }
}
