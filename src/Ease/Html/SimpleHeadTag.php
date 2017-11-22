<?php

namespace Ease\Html;

/**
 * Siple HTML head tag class.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class SimpleHeadTag extends PairTag
{
    /**
     * Content type of webpage.
     *
     * @var string
     */
    public static $contentType = 'text/html';

    /**
     * Content Charset
     * Znaková sada obsahu.
     *
     * @var string
     */
    public $charSet = 'utf-8';

    /**
     * head tag with defined meta http-equiv content type.
     *
     * @param mixed $contents   vkládaný obsah
     * @param array $properties parametry tagu
     */
    public function __construct($contents = null, $properties = [])
    {
        parent::__construct('head', $properties, $contents);
        $this->addItem('<meta http-equiv="Content-Type" content="'.self::$contentType.'; charset='.$this->charSet.'" />');
    }
}
