<?php

namespace Ease\TWB;

/**
 * Odznak bootstrapu.
 */
class Badge extends \Ease\Html\SpanTag
{
    /**
     * Návěstí bootstrapu.
     *
     * @link http://getbootstrap.com/components/#badges
     *
     * @param mixed $content
     * @param array $properties
     */
    public function __construct($content = null, $properties = null)
    {
        parent::__construct(null, $content, $properties);
        $this->addTagClass('badge');
    }
}
