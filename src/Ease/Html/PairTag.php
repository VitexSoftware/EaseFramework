<?php

namespace Ease\Html;

/**
 * Obecný párový HTML tag.
 *
 * @author     Vitex <vitex@hippy.cz>
 */
class PairTag extends Tag
{
    /**
     * Character to close tag.
     *
     * @var string
     */
    public $trail = '';

    /**
     * Common pair tag.
     *
     * @param string       $tagType       typ tagu
     * @param array|string $tagProperties parametry tagu
     * @param mixed        $content       Content to insert into tag
     */
    public function __construct($tagType = null, $tagProperties = null,
                                $content = null)
    {
        parent::__construct($tagType, $tagProperties);
        if (!empty($content)) {
            $this->addItem($content);
        }
    }

    /**
     * Render tag and its contents.
     */
    public function draw()
    {
        $this->tagBegin();
        $this->drawAllContents();
        $this->tagEnclousure();
    }

    /**
     * Show pair tag begin.
     */
    public function tagBegin()
    {
        parent::draw();
    }

    /**
     * Show pair tag ending.
     */
    public function tagEnclousure()
    {
        echo '</'.$this->tagType.'>';
    }
}
