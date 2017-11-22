<?php

namespace Ease\TWB;

/**
 * Odznak bootstrapu.
 */
class Badge extends \Ease\Html\Span
{

    /**
     * Návěstí bootstrapu.
     *
     * @link http://getbootstrap.com/components/#badges
     *
     * @param mixed $content
     * @param array $properties
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct($content, $properties);
        $this->addTagClass('badge');
    }
}
