<?php

namespace Ease\Html;

/**
 * iFrame element.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class IframeTag extends PairTag
{
    public $tagType = 'iframe';

    /**
     * iFrame element.
     *
     * @param string $src        content url
     * @param array  $properties HTML tag proberties
     */
    public function __construct($src, $properties = null)
    {
        if (is_null($properties)) {
            $properties = ['src' => $src];
        } else {
            $properties['src'] = $src;
        }
        $this->setTagProperties($properties);
        parent::__construct();
    }
}