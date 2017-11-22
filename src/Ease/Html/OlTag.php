<?php

namespace Ease\Html;

/**
 * HTML unsorted list.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class OlTag extends UlTag
{

    /**
     * Vytvori OL container.
     *
     * @param mixed $ulContents položky seznamu
     * @param array $properties parametry tagu
     */
    public function __construct($ulContents = null, $properties = [])
    {
        parent::__construct($ulContents, $properties);
        $this->setTagType('ol');
    }
}
