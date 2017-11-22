<?php

namespace Ease\TWB4;

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
     * @param string $type       success|info|warning|danger
     * @param mixed $content     to insert in
     * @param array $properties  additional
     */
    public function __construct($type, $content = null, $properties = [])
    {
        parent::__construct($content, $properties);
        $this->addTagClass('badge badge-'.$type);
    }
}
